<?php

namespace Database\Seeders;

use App\Models\{Perusahaan, Penjual, Supir, TransaksiDo, TransaksiOperasional};
use App\Enums\KategoriOperasional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class SimulasiDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Target CV SUCCESS MANDIRI (ID: 1)
        $perusahaan = Perusahaan::find(1);
        if (!$perusahaan) {
            $perusahaan = Perusahaan::first();
        }

        $perusahaanId = $perusahaan->id;
        // Tanggal simulasi untuk dashboard Hari Ini
        $tanggalSimulasi = Carbon::today();

        // Bersihkan data lama untuk tanggal ini agar tidak duplikat
        TransaksiDo::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', Carbon::today())
            ->forceDelete();
            
        TransaksiOperasional::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', Carbon::today())
            ->forceDelete();

        // **PENTING: Reset Saldo agar simulasi akurat**
        $perusahaan->update(['saldo' => 0]); 
        \App\Models\JurnalKeuangan::where('perusahaan_id', $perusahaanId)->delete();

        // 2. Operasional: Saldo Awal & Pengeluaran
        // Tambah Saldo 100 Juta (Wendi Tarik Tunai)
        TransaksiOperasional::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $tanggalSimulasi->copy()->setHour(8),
            'kategori' => KategoriOperasional::TAMBAH_SALDO,
            'nominal' => 95215000, // Kalibrasi agar Saldo Akhir = 953.470
            'keterangan' => 'WENDI TARIK TUNAI (Saldo Awal)',
        ]);

        // Pengeluaran Belanja
        TransaksiOperasional::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $tanggalSimulasi->copy()->setHour(17),
            'kategori' => KategoriOperasional::LAIN_LAIN,
            'nominal' => 50000,
            'keterangan' => 'BELANJA',
        ]);

        // 3. Data 19 Baris DO dari Laporan Keuangan Harian
        $dataDo = [
            ['penjual' => 'Pijan', 'supir' => 'Pijan', 'tonase' => 1105, 'harga' => 3500],
            ['penjual' => 'Sianifar', 'supir' => 'Sianifar', 'tonase' => 2041, 'harga' => 3500],
            ['penjual' => 'Man Sagala', 'supir' => 'Man Sagala', 'tonase' => 370, 'harga' => 3500],
            ['penjual' => 'Rego', 'supir' => 'Rego', 'tonase' => 2879, 'harga' => 3500],
            ['penjual' => 'Emen', 'supir' => 'Emen', 'tonase' => 1159, 'harga' => 3500],
            ['penjual' => 'Meyki', 'supir' => 'Meyki', 'tonase' => 1134, 'harga' => 3500],
            ['penjual' => 'Meyki', 'supir' => 'Meyki', 'tonase' => 867, 'harga' => 3500],
            ['penjual' => 'Rival', 'supir' => 'Rival', 'tonase' => 1506, 'harga' => 3500],
            ['penjual' => 'Hakimi', 'supir' => 'Ap', 'tonase' => 1916, 'harga' => 3500],
            ['penjual' => 'Ropi', 'supir' => 'Ropi', 'tonase' => 3503, 'harga' => 3510],
            ['penjual' => 'Nasferi', 'supir' => 'Nasferi', 'tonase' => 1830, 'harga' => 3500],
            ['penjual' => 'Joko', 'supir' => 'Joko', 'tonase' => 591, 'harga' => 3500],
            ['penjual' => 'Ana', 'supir' => 'Anto', 'tonase' => 5423, 'harga' => 3500],
            [
                'penjual' => 'Sudurdin', 'supir' => 'Anto', 'tonase' => 2168, 'harga' => 3500,
                'biaya' => 726000, 'hutang' => 500000, 'cara_bayar' => 'transfer'
            ],
            ['penjual' => 'Febri', 'supir' => 'Febri', 'tonase' => 411, 'harga' => 3500],
            [
                'penjual' => 'Gustia harani', 'supir' => 'Jeki', 'tonase' => 2387, 'harga' => 3500,
                'biaya' => 100000, 'cara_bayar' => 'transfer'
            ],
            [
                'penjual' => 'Rivaldi', 'supir' => 'Rival', 'tonase' => 1296, 'harga' => 3500,
                'cara_bayar' => 'transfer'
            ],
            [
                'penjual' => 'Irman', 'supir' => 'Nopi', 'tonase' => 6761, 'harga' => 3500,
                'biaya' => 2046000, 'cara_bayar' => 'transfer'
            ],
            ['penjual' => 'Andes', 'supir' => 'Andes', 'tonase' => 1352, 'harga' => 3500],
        ];

        foreach ($dataDo as $index => $item) {
            try {
                $penjualModel = Penjual::firstOrCreate(
                    ['nama' => $item['penjual'], 'perusahaan_id' => $perusahaanId],
                    ['perusahaan_id' => $perusahaanId]
                );

                $bayarHutang = $item['hutang'] ?? 0;
                if ($bayarHutang > 0 && $penjualModel->hutang < $bayarHutang) {
                    $penjualModel->update(['hutang' => $bayarHutang]);
                }

                $idSupir = null;
                if ($item['supir'] && $item['supir'] !== '-') {
                    $supirModel = Supir::firstOrCreate(
                        ['nama' => $item['supir'], 'perusahaan_id' => $perusahaanId],
                        ['perusahaan_id' => $perusahaanId]
                    );
                    $idSupir = $supirModel->id;
                }

                $subTotal = $item['tonase'] * $item['harga'];
                $biayaLain = $item['biaya'] ?? 0;
                $sisaBayar = $subTotal - $biayaLain - $bayarHutang;

                TransaksiDo::create([
                    'perusahaan_id' => $perusahaanId,
                    'tanggal' => $tanggalSimulasi->copy()->setHour(9)->addMinutes($index * 5),
                    'penjual_id' => $penjualModel->id,
                    'supir_id' => $idSupir,
                    'no_polisi' => 'BK ' . (3000 + $index) . ' SM',
                    'tonase' => $item['tonase'],
                    'harga_satuan' => $item['harga'],
                    'sub_total' => $subTotal,
                    'biaya_lain' => $biayaLain,
                    'upah_bongkar' => 0,
                    'pembayaran_hutang' => $bayarHutang,
                    'sisa_bayar' => $sisaBayar,
                    'cara_bayar' => $item['cara_bayar'] ?? 'tunai',
                    'keterangan_pembayaran' => 'Lap ' . Carbon::today()->format('d/m') . ' - Row ' . ($index + 1),
                ]);
            } catch (\Exception $e) {
                $this->command->error("Gagal pada baris " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        $this->command->info('Simulasi Laporan (19 Baris + 100jt) di CV SUCCESS MANDIRI berhasil disuntikkan.');
    }
}
