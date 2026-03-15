<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiDo;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\TransaksiOperasional;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Enums\KategoriOperasional;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
                'saldo' => 1000000000,
            ])]);
        }

        foreach ($perusahaans as $perusahaan) {
            $this->command->info("Starting simulation for company: {$perusahaan->name}");
            $perusahaan->update(['saldo' => 1000000000]);
            $perusahaanId = $perusahaan->id;

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

            $penjualIds = Penjual::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
            $supirIds = Supir::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();

            // 3. Disable Observers
            TransaksiDo::unsetEventDispatcher();
            TransaksiOperasional::unsetEventDispatcher();
            JurnalKeuangan::unsetEventDispatcher();

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

                $this->createMonthlyOperasional($currentDate, $perusahaanId, $daysInMonth);
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
    }

    private function createMonthlyOperasional($monthDate, $perusahaanId, $daysInMonth)
    {
        // 1. Pemasukan Bulanan (e.g., Setoran Modal or others)
        $pemasukanCount = rand(1, 3);
        for ($i = 0; $i < $pemasukanCount; $i++) {
            $day = rand(1, $daysInMonth);
            $tanggal = (clone $monthDate)->setDay($day);
            
            $this->seedOp($tanggal, 'Pemasukan Bulanan ' . $monthDate->format('F'), rand(5000000, 20000000), KategoriOperasional::TAMBAH_SALDO, $perusahaanId);
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
            
            $this->seedOp($tanggal, "Operasional {$kat->label()} " . $monthDate->format('F'), rand(200000, 2000000), $kat, $perusahaanId);
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
