<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiDo;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\Pekerja;
use App\Models\TransaksiOperasional;
use App\Models\JurnalKeuangan;
use App\Models\PembayaranHutang;
use App\Models\MutasiHutang;
use App\Models\Perusahaan;
use App\Enums\KategoriOperasional;
use App\Enums\TipeNama;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get all Perusahaan
        $perusahaans = Perusahaan::all();

        if ($perusahaans->isEmpty()) {
            $this->command->warn("No Perusahaan found! Creating a default one...");
            $perusahaans = collect([Perusahaan::create([
                'name' => 'SUKSES MANDIRI',
                'alamat' => 'Jl. Lintas Sumatera',
                'telepon' => '08123456789',
                'saldo' => 800000000,
            ])]);
        }

        foreach ($perusahaans as $perusahaan) {
            $this->command->info("Starting simulation for company: {$perusahaan->name}");
            $perusahaan->update(['saldo' => 1000000000]);
            $perusahaanId = $perusahaan->id;

            // 1.5 Cleanup existing simulation data for this company (Force Delete to avoid unique constraint issues with soft deletes)
            $this->command->info("Cleaning up existing simulation data for {$perusahaan->name}...");
            TransaksiDo::where('perusahaan_id', $perusahaanId)->forceDelete();
            TransaksiOperasional::where('perusahaan_id', $perusahaanId)->forceDelete();
            JurnalKeuangan::where('perusahaan_id', $perusahaanId)->forceDelete();
            PembayaranHutang::where('perusahaan_id', $perusahaanId)->forceDelete();
            MutasiHutang::where('perusahaan_id', $perusahaanId)->delete(); // MutasiHutang doesn't use soft deletes usually

            // Reset balances
            Penjual::where('perusahaan_id', $perusahaanId)->update(['hutang' => 0]);
            Supir::where('perusahaan_id', $perusahaanId)->update(['hutang' => 0]);
            Pekerja::where('perusahaan_id', $perusahaanId)->update(['hutang' => 0, 'pendapatan' => 0]);

            // 2. Ensure we have data for this tenant
            if (Penjual::where('perusahaan_id', $perusahaanId)->count() === 0) {
                $this->command->info("Creating sample Penjual for {$perusahaan->name}...");
                for ($i = 0; $i < 5; $i++) {
                    Penjual::create([
                        'nama' => "Penjual {$perusahaanId}-" . ($i + 1),
                        'alamat' => 'Alamat Penjual ' . ($i + 1),
                        'telepon' => '081234567' . $i,
                        'is_active' => true,
                        'perusahaan_id' => $perusahaanId,
                    ]);
                }
            }

            if (Supir::where('perusahaan_id', $perusahaanId)->count() === 0) {
                $this->command->info("Creating sample Supir for {$perusahaan->name}...");
                for ($i = 0; $i < 3; $i++) {
                    Supir::create([
                        'nama' => "Supir {$perusahaanId}-" . ($i + 1),
                        'alamat' => 'Alamat Supir ' . ($i + 1),
                        'telepon' => '089876543' . $i,
                        'perusahaan_id' => $perusahaanId,
                    ]);
                }
            }

            if (Pekerja::where('perusahaan_id', $perusahaanId)->count() === 0) {
                $this->command->info("Creating sample Pekerja for {$perusahaan->name}...");
                for ($i = 0; $i < 3; $i++) {
                    Pekerja::create([
                        'nama' => "Pekerja {$perusahaanId}-" . ($i + 1),
                        'alamat' => 'Alamat Pekerja ' . ($i + 1),
                        'telepon' => '087765432' . $i,
                        'perusahaan_id' => $perusahaanId,
                    ]);
                }
            }

            $penjualIds = Penjual::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
            $supirIds = Supir::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
            $pekerjaIds = Pekerja::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();

            // 3. Disable Observers
            TransaksiDo::unsetEventDispatcher();
            TransaksiOperasional::unsetEventDispatcher();
            JurnalKeuangan::unsetEventDispatcher();
            PembayaranHutang::unsetEventDispatcher();
            MutasiHutang::unsetEventDispatcher();

            // 4. Generate data for the last 6 months
            $startDate = now()->subMonths(6)->startOfMonth();
            $currentDate = clone $startDate;

            while ($currentDate < now()->startOfDay()) {
                $count = rand(15, 30);
                $daysInMonth = $currentDate->daysInMonth;

                for ($i = 1; $i <= $count; $i++) {
                    $targetDay = rand(1, $daysInMonth);
                    $tanggal = (clone $currentDate)->setDay($targetDay)->setHour(rand(8, 17))->setMinute(rand(0, 59));

                    if ($tanggal >= now()->subDays(2)->startOfDay()) continue;

                    $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
                }

                $this->createMonthlyOperasional($currentDate, $perusahaanId, $daysInMonth, $supirIds, $pekerjaIds);
                $this->createMonthlyPayments($currentDate, $perusahaanId, $daysInMonth, $penjualIds, $supirIds, $pekerjaIds);
                $currentDate->addMonth();
            }

            // 5. Generate data for Yesterday specifically
            $this->command->info("Generating data for Yesterday for {$perusahaan->name}...");
            $yesterday = now()->subDay();
            for ($i = 1; $i <= 5; $i++) {
                $tanggal = (clone $yesterday)->setHour(8 + $i)->setMinute(rand(0, 59));
                $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
            }

            // 6. Generate data for Today specifically
            $this->command->info("Generating data for Today for {$perusahaan->name}...");
            $today = now();
            for ($i = 1; $i <= 3; $i++) {
                $tanggal = (clone $today)->setHour(8 + $i)->setMinute(rand(0, 59));
                $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
            }
        }

        $this->command->info("Simulasi data berhasil dibuat untuk semua perusahaan!");
    }

    private function createMonthlyTransaction($tanggal, $index, $penjualIds, $supirIds, $perusahaanId)
    {
        $tonase = rand(1000, 10000);
        $harga = rand(3000, 3500);
        $subTotal = $tonase * $harga;
        $caraBayar = ['tunai', 'transfer', 'cair di luar', 'belum dibayar'][rand(0, 3)];

        $penjualId = $penjualIds[array_rand($penjualIds)];
        $supirId = $supirIds[array_rand($supirIds)];

        $nomor = 'DO-' . $perusahaanId . '-' . $tanggal->format('Ymd') . '-' . Str::padLeft($index, 4, '0');

        $noPolisi = ['B ' . rand(1000, 9999) . ' ABC', 'A ' . rand(1000, 9999) . ' XYZ', 'D ' . rand(1000, 4999) . ' DEF'][rand(0, 2)];

        $transaksi = TransaksiDo::create([
            'nomor' => $nomor,
            'tanggal' => $tanggal,
            'penjual_id' => $penjualId,
            'supir_id' => $supirId,
            'no_polisi' => $noPolisi,
            'tonase' => $tonase,
            'harga_satuan' => $harga,
            'sub_total' => $subTotal,
            'upah_bongkar' => 50000,
            'biaya_lain' => 0,
            'sisa_bayar' => $subTotal,
            'pembayaran_hutang' => rand(0, 1) ? rand(100000, 500000) : 0,
            'cara_bayar' => $caraBayar,
            'perusahaan_id' => $perusahaanId,
        ]);

        JurnalKeuangan::create([
            'tanggal' => $tanggal,
            'jenis_transaksi' => 'Pengeluaran',
            'kategori' => 'DO',
            'sub_kategori' => 'Pembayaran DO',
            'nominal' => $subTotal,
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksi->id,
            'nomor_referensi' => $transaksi->nomor,
            'pihak_terkait' => $transaksi->penjual?->nama ?? 'Penjual Simulasi',
            'tipe_pihak' => 'penjual',
            'cara_pembayaran' => $caraBayar,
            'keterangan' => "Simulasi Transaksi {$nomor}",
            'mempengaruhi_kas' => $caraBayar === 'tunai',
            'perusahaan_id' => $perusahaanId,
        ]);

        if ($transaksi->pembayaran_hutang > 0) {
            $penjual = $transaksi->penjual;
            if ($penjual) {
                $penjual->increment('hutang', $transaksi->pembayaran_hutang);
                
                MutasiHutang::create([
                    'perusahaan_id' => $perusahaanId,
                    'pihak_id' => $penjual->id,
                    'pihak_type' => Penjual::class,
                    'tanggal' => $tanggal,
                    'tipe' => 'HUTANG_MASUK',
                    'nominal' => $transaksi->pembayaran_hutang,
                    'saldo_akhir' => $penjual->hutang,
                    'referensi_id' => $transaksi->id,
                    'referensi_type' => TransaksiDo::class,
                    'keterangan' => "Hutang dari Transaksi {$nomor}",
                ]);
            }
        }
    }

    private function createMonthlyPayments($monthDate, $perusahaanId, $daysInMonth, $penjualIds, $supirIds, $pekerjaIds)
    {
        $entities = [
            ['model' => Penjual::class, 'ids' => $penjualIds, 'type' => TipeNama::PENJUAL],
            ['model' => Supir::class, 'ids' => $supirIds, 'type' => TipeNama::SUPIR],
            ['model' => Pekerja::class, 'ids' => $pekerjaIds, 'type' => TipeNama::PEKERJA],
        ];

        foreach ($entities as $entity) {
            $eligibleIds = ($entity['model'])::whereIn('id', $entity['ids'])
                ->where('hutang', '>', 0)
                ->pluck('id')
                ->toArray();

            if (empty($eligibleIds)) continue;

            $paymentCount = rand(1, min(count($eligibleIds), 3));
            $selectedIds = (array) array_rand(array_flip($eligibleIds), $paymentCount);

            foreach ($selectedIds as $id) {
                $record = ($entity['model'])::find($id);
                if (!$record || $record->hutang <= 0) continue;

                $nominal = rand(min(100000, $record->hutang), min(1000000, $record->hutang));
                $day = rand(1, $daysInMonth);
                $tanggal = (clone $monthDate)->setDay($day);

                // Create Operational Transaction first for the payment
                $op = TransaksiOperasional::create([
                    'tanggal' => $tanggal,
                    'operasional' => 'pemasukan',
                    'kategori' => KategoriOperasional::BAYAR_HUTANG,
                    'tipe_nama' => $entity['type']->value,
                    'pihak_id' => $id,
                    'pihak_type' => $entity['model'],
                    'nominal' => $nominal,
                    'keterangan' => "Pelunasan Hutang Simulasi",
                    'perusahaan_id' => $perusahaanId,
                ]);

                $payment = PembayaranHutang::create([
                    'tanggal' => $tanggal,
                    'nominal' => $nominal,
                    'tipe_nama' => $entity['type'],
                    'penjual_id' => $entity['type'] === TipeNama::PENJUAL ? $id : null,
                    'supir_id' => $entity['type'] === TipeNama::SUPIR ? $id : null,
                    'pekerja_id' => $entity['type'] === TipeNama::PEKERJA ? $id : null,
                    'operasional_id' => $op->id,
                    'keterangan' => "Pelunasan Hutang Simulasi",
                    'perusahaan_id' => $perusahaanId,
                ]);

                $record->decrement('hutang', $nominal);

                MutasiHutang::create([
                    'perusahaan_id' => $perusahaanId,
                    'pihak_id' => $id,
                    'pihak_type' => $entity['model'],
                    'tanggal' => $tanggal,
                    'tipe' => 'HUTANG_KELUAR',
                    'nominal' => $nominal,
                    'saldo_akhir' => $record->hutang,
                    'referensi_id' => $payment->id,
                    'referensi_type' => PembayaranHutang::class,
                    'keterangan' => "Pelunasan Hutang Simulasi",
                ]);

                JurnalKeuangan::create([
                    'tanggal' => $tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'Operasional',
                    'sub_kategori' => 'Bayar Hutang',
                    'nominal' => $nominal,
                    'sumber_transaksi' => 'Operasional',
                    'referensi_id' => $op->id,
                    'pihak_terkait' => $record->nama,
                    'tipe_pihak' => $entity['type']->value,
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => "Terima Pembayaran Hutang: {$record->nama}",
                    'mempengaruhi_kas' => true,
                    'perusahaan_id' => $perusahaanId,
                ]);
            }
        }
    }

    private function createMonthlyOperasional($monthDate, $perusahaanId, $daysInMonth, $supirIds, $pekerjaIds)
    {
        // 1. Pemasukan Bulanan (e.g., Setoran Modal or others)
        $pemasukanCount = rand(1, 3);
        for ($i = 0; $i < $pemasukanCount; $i++) {
            $day = rand(1, $daysInMonth);
            $tanggal = (clone $monthDate)->setDay($day);

            $monthName = [
                'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
            ][$monthDate->format('F')];

            $this->seedOp($tanggal, 'Pemasukan Bulanan ' . $monthName, rand(5000000, 20000000), KategoriOperasional::TAMBAH_SALDO, $perusahaanId);
        }

        // 2. Pengeluaran Bulanan (e.g., Listrik, Gaji, etc)
        $pengeluaranCount = rand(3, 8);
        $kategoriPengeluaran = [
            KategoriOperasional::BAHAN_BAKAR,
            KategoriOperasional::PERAWATAN,
            KategoriOperasional::LAIN_LAIN,
            KategoriOperasional::UANG_JALAN,
        ];

        for ($i = 0; $i < $pengeluaranCount; $i++) {
            $day = rand(1, $daysInMonth);
            $tanggal = (clone $monthDate)->setDay($day);
            $kat = $kategoriPengeluaran[array_rand($kategoriPengeluaran)];

            $monthName = [
                'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
            ][$monthDate->format('F')];

            $this->seedOp($tanggal, "Operasional {$kat->label()} " . $monthName, rand(200000, 2000000), $kat, $perusahaanId);
        }

        // 3. Pinjaman Simulation (Debt entry)
        $pinjamanCount = rand(1, 3);
        $entitiesForPinjaman = [
            ['model' => Supir::class, 'ids' => $supirIds, 'type' => 'supir'],
            ['model' => Pekerja::class, 'ids' => $pekerjaIds, 'type' => 'pekerja'],
        ];

        foreach ($entitiesForPinjaman as $entity) {
            for ($i = 0; $i < $pinjamanCount; $i++) {
                $id = $entity['ids'][array_rand($entity['ids'])];
                $record = ($entity['model'])::find($id);
                $nominal = rand(500000, 2000000);
                $day = rand(1, $daysInMonth);
                $tanggal = (clone $monthDate)->setDay($day);

                $op = TransaksiOperasional::create([
                    'tanggal' => $tanggal,
                    'operasional' => 'pengeluaran',
                    'kategori' => KategoriOperasional::PINJAMAN,
                    'tipe_nama' => $entity['type'],
                    'pihak_id' => $id,
                    'pihak_type' => $entity['model'],
                    'nominal' => $nominal,
                    'keterangan' => "Pinjaman Simulasi",
                    'perusahaan_id' => $perusahaanId,
                ]);

                $record->increment('hutang', $nominal);

                MutasiHutang::create([
                    'perusahaan_id' => $perusahaanId,
                    'pihak_id' => $id,
                    'pihak_type' => $entity['model'],
                    'tanggal' => $tanggal,
                    'tipe' => 'HUTANG_MASUK',
                    'nominal' => $nominal,
                    'saldo_akhir' => $record->hutang,
                    'referensi_id' => $op->id,
                    'referensi_type' => TransaksiOperasional::class,
                    'keterangan' => "Pinjaman Simulasi",
                ]);

                JurnalKeuangan::create([
                    'tanggal' => $tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'Operasional',
                    'sub_kategori' => 'Pinjaman',
                    'nominal' => $nominal,
                    'sumber_transaksi' => 'Operasional',
                    'referensi_id' => $op->id,
                    'pihak_terkait' => $record->nama,
                    'tipe_pihak' => $entity['type'],
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => "Pinjaman ke: {$record->nama}",
                    'mempengaruhi_kas' => true,
                    'perusahaan_id' => $perusahaanId,
                ]);
            }
        }
    }

    private function seedOp($tanggal, $keterangan, $nominal, KategoriOperasional $kategori, $perusahaanId)
    {
        $jenis = $kategori->getJenisOperasional();

        $op = TransaksiOperasional::create([
            'tanggal' => $tanggal,
            'operasional' => $jenis,
            'kategori' => $kategori,
            'tipe_nama' => 'user',
            'user_id' => 1,
            'nominal' => $nominal,
            'keterangan' => $keterangan,
            'perusahaan_id' => $perusahaanId,
        ]);

        JurnalKeuangan::create([
            'tanggal' => $tanggal,
            'jenis_transaksi' => ucfirst($jenis),
            'kategori' => 'Operasional',
            'sub_kategori' => $kategori->label(),
            'nominal' => $nominal,
            'sumber_transaksi' => 'Operasional',
            'referensi_id' => $op->id,
            'pihak_terkait' => 'System Simulation',
            'tipe_pihak' => 'user',
            'cara_pembayaran' => 'tunai',
            'keterangan' => $keterangan,
            'mempengaruhi_kas' => true,
            'perusahaan_id' => $perusahaanId,
        ]);
    }
}
