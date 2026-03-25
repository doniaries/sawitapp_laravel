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
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            $perusahaan = Perusahaan::create(['nama' => 'SUCCESS MANDIRI']);
        }

        $perusahaanId = $perusahaan->id;
        $tanggalSimulasi = Carbon::create(2026, 3, 25, 0, 0, 0);

        // Bersihkan data lama untuk tanggal ini agar tidak duplikat/bentrok nomor
        TransaksiDo::whereDate('tanggal', '2026-03-25')->forceDelete();
        TransaksiOperasional::whereDate('tanggal', '2026-03-25')->forceDelete();

        // 1. Operasional
        TransaksiOperasional::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $tanggalSimulasi->copy()->setHour(8),
            'kategori' => KategoriOperasional::TAMBAH_SALDO,
            'nominal' => 100000000,
            'keterangan' => 'WENDI TARIK TUNAI (Penambahan Saldo)',
        ]);

        TransaksiOperasional::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $tanggalSimulasi->copy()->setHour(17),
            'kategori' => KategoriOperasional::LAIN_LAIN,
            'nominal' => 50000,
            'keterangan' => 'BELANJA',
        ]);

        // 2. Data 19 Baris DO dari Excel
        $dataDo = [
            ['penjual' => '', 'supir' => 'PIAN', 'tonase' => 1105, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'SIANIPAR', 'tonase' => 2041, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'SAGALA', 'tonase' => 370, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'REGO', 'tonase' => 2879, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'EMEN', 'tonase' => 1159, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'MEKI', 'tonase' => 1134, 'harga' => 3500],
            ['penjual' => '', 'supir' => '-', 'tonase' => 867, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'RIVAL', 'tonase' => 1506, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'HAKIMI', 'tonase' => 1916, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'ROPI', 'tonase' => 3503, 'harga' => 3510],
            ['penjual' => '', 'supir' => 'NASPERI', 'tonase' => 1830, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'JOKO', 'tonase' => 591, 'harga' => 3500],
            [
                'penjual' => 'SUDIRDIN', 'supir' => 'ANTO', 'tonase' => 2168, 'harga' => 3500,
                'biaya' => 726000, 'hutang' => 500000, 'cara_bayar' => 'transfer'
            ],
            ['penjual' => '', 'supir' => 'FEBRI', 'tonase' => 411, 'harga' => 3500],
            ['penjual' => '', 'supir' => 'ANTO/ANA', 'tonase' => 5423, 'harga' => 3500],
            [
                'penjual' => 'OUSTIA', 'supir' => 'JEKI', 'tonase' => 2387, 'harga' => 3500,
                'biaya' => 100000, 'cara_bayar' => 'transfer'
            ],
            [
                'penjual' => 'RIVALDI', 'supir' => '-', 'tonase' => 1296, 'harga' => 3500,
                'cara_bayar' => 'transfer'
            ],
            [
                'penjual' => 'IRMAN', 'supir' => 'NOPI', 'tonase' => 6761, 'harga' => 3500,
                'biaya' => 2046000, 'cara_bayar' => 'transfer'
            ],
            ['penjual' => '', 'supir' => 'ANDES', 'tonase' => 1352, 'harga' => 3500],
        ];

        $penjualCounter = 1;
        foreach ($dataDo as $index => $item) {
            try {
                $namaPenjual = $item['penjual'];
                if (empty($namaPenjual)) {
                    $namaPenjual = 'Penjual ' . $penjualCounter++;
                }

                $penjual = Penjual::firstOrCreate(
                    ['nama' => $namaPenjual, 'perusahaan_id' => $perusahaanId],
                    ['perusahaan_id' => $perusahaanId]
                );

                $bayarHutang = $item['hutang'] ?? 0;
                if ($bayarHutang > 0 && $penjual->hutang < $bayarHutang) {
                    $penjual->update(['hutang' => $bayarHutang]);
                }

                $idSupir = null;
                if ($item['supir'] && $item['supir'] !== '-') {
                    $supir = Supir::firstOrCreate(
                        ['nama' => $item['supir'], 'perusahaan_id' => $perusahaanId],
                        ['perusahaan_id' => $perusahaanId]
                    );
                    $idSupir = $supir->id;
                }

                $subTotal = $item['tonase'] * $item['harga'];
                $biayaLain = $item['biaya'] ?? 0;
                $sisaBayar = $subTotal - $biayaLain - $bayarHutang;

                TransaksiDo::create([
                    'perusahaan_id' => $perusahaanId,
                    'tanggal' => $tanggalSimulasi->copy()->setHour(9)->addMinutes($index * 5),
                    'penjual_id' => $penjual->id,
                    'supir_id' => $idSupir,
                    'no_polisi' => 'BK ' . (1000 + $index) . ' SM',
                    'tonase' => $item['tonase'],
                    'harga_satuan' => $item['harga'],
                    'sub_total' => $subTotal,
                    'biaya_lain' => $biayaLain,
                    'upah_bongkar' => 0,
                    'pembayaran_hutang' => $bayarHutang,
                    'sisa_bayar' => $sisaBayar,
                    'cara_bayar' => $item['cara_bayar'] ?? 'tunai',
                    'keterangan_pembayaran' => 'Seeder Simulasi Row ' . ($index + 1),
                ]);
            } catch (\Exception $e) {
                $this->command->error("Gagal pada baris " . ($index + 1) . ": " . $e->getMessage());
                Log::error("Seeder Error Row " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        $this->command->info('Simulasi data Excel (19 Baris) berhasil disuntikkan.');
    }
}
