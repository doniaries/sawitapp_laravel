<?php

namespace Database\Seeders;

use App\Models\{Perusahaan, Penjual, Supir, TransaksiDo, TransaksiOperasional, JurnalKeuangan, TambahSaldo};
use App\Enums\KategoriOperasional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, App};
use Carbon\Carbon;
use Faker\Factory as Faker;

class SimulasiDataSeeder extends Seeder
{
    public function run(): void
    {
        if (App::isProduction()) {
            $this->command->alert('ANDA SEDANG DI LINGKUNGAN PRODUCTION!');
            if (!$this->command->confirm('Seeder ini akan MENGHAPUS seluruh data transaksi CV SUCCESS MANDIRI. Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->command->info('Seeding dibatalkan.');
                return;
            }
        }

        $faker = Faker::create('id_ID');
        
        // Fix IDE: Tambahkan parameter kedua ['*'] pada find
        $perusahaan = Perusahaan::find(1, ['*']) ?? Perusahaan::first(['*']);
        if (!$perusahaan) return;

        $perusahaanId = $perusahaan->id;

        $this->command->info('Membersihkan data lama CV SUCCESS MANDIRI...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TransaksiDo::where('perusahaan_id', '=', $perusahaanId, 'and')->forceDelete();
        TransaksiOperasional::where('perusahaan_id', '=', $perusahaanId, 'and')->forceDelete();
        JurnalKeuangan::where('perusahaan_id', '=', $perusahaanId, 'and')->forceDelete();
        \App\Models\PembayaranHutang::where('perusahaan_id', '=', $perusahaanId, 'and')->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $perusahaan->update(['saldo' => 50000000]); 

        $penjuals = Penjual::where('perusahaan_id', '=', $perusahaanId, 'and')->get(['*']);
        $supirIds = Supir::where('perusahaan_id', '=', $perusahaanId, 'and')->pluck('id')->toArray();
        $kategoriOps = KategoriOperasional::cases();

        if ($penjuals->isEmpty() || empty($supirIds)) {
            $this->command->error('Error: Penjual atau Supir tidak ditemukan.');
            return;
        }

        foreach ($penjuals as $p) {
            $p->update(['hutang' => rand(0, 1) ? rand(2000000, 8000000) : 0]);
        }

        $now = now();
        $daysToSeed = [
            ['date' => $now->copy()->subDays(2), 'do_count' => 50, 'ops_count' => 20],
            ['date' => $now->copy()->subDays(1), 'do_count' => 100, 'ops_count' => 30],
            ['date' => $now->copy(), 'do_count' => 10, 'ops_count' => 15],
        ];

        $totalDo = array_sum(array_column($daysToSeed, 'do_count'));
        $this->command->info("Menghasilkan data untuk 3 hari terakhir (Total DO: {$totalDo})...");
        $progressBar = $this->command->getOutput()->createProgressBar($totalDo + 65); // DO + Ops + Saldo
        $progressBar->start();

        foreach ($daysToSeed as $day) {
            $currentDate = $day['date'];
            
            // 1. Injeksi Modal (1 per hari jika ada)
            if ($faker->boolean(70)) {
                TambahSaldo::create([
                    'perusahaan_id' => $perusahaanId,
                    'user_id' => 1,
                    'tanggal' => $currentDate->copy()->setHour(9),
                    'nominal' => $faker->randomElement([10000000, 20000000, 50000000]),
                    'keterangan' => 'Injeksi Modal Harian (' . $currentDate->format('d/m') . ')',
                ]);
            }

            // 2. Transaksi DO
            for ($i = 0; $i < $day['do_count']; $i++) {
                $tanggalTrans = $currentDate->copy()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $penjual = $penjuals->random();
                $tonase = $faker->numberBetween(1000, 2500); 
                $harga = 3600;
                $subTotal = $tonase * $harga;
                
                $sisaHutangReal = (float) $penjual->hutang;
                $upahBongkar = $faker->numberBetween(50000, 100000);
                $biayaLain = $faker->numberBetween(100000, 500000);
                
                $maxBayar = (int)($subTotal / 2);
                $bayarHutang = ($sisaHutangReal > 200000) 
                                ? $faker->numberBetween(200000, min($sisaHutangReal, $maxBayar)) 
                                : 0;
                
                TransaksiDo::create([
                    'perusahaan_id' => $perusahaanId,
                    'user_id' => 1,
                    'nomor' => 'DO-' . $tanggalTrans->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'tanggal' => $tanggalTrans,
                    'penjual_id' => $penjual->id,
                    'supir_id' => $faker->randomElement($supirIds),
                    'no_polisi' => 'BA ' . $faker->numberBetween(1000, 9999) . ' ' . $faker->lexify('??'),
                    'tonase' => $tonase,
                    'harga_satuan' => $harga,
                    'sub_total' => $subTotal,
                    'upah_bongkar' => $upahBongkar,
                    'biaya_lain' => $biayaLain,
                    'keterangan_biaya_lain' => 'Biaya Simulasi',
                    'hutang_awal' => $sisaHutangReal,
                    'pembayaran_hutang' => $bayarHutang,
                    'sisa_hutang_penjual' => max(0, $sisaHutangReal - $bayarHutang),
                    'sisa_bayar' => max(0, $subTotal - $bayarHutang - $upahBongkar - $biayaLain),
                    'cara_bayar' => $faker->randomElement(['tunai', 'transfer']),
                    'nominal_tunai' => 0,
                    'keterangan_pembayaran' => 'DO ' . ($currentDate->isToday() ? 'HARI INI' : $currentDate->format('d/m/Y')),
                ]);

                $penjual->hutang = max(0, $sisaHutangReal - $bayarHutang);
                $progressBar->advance();
            }

            // 3. Transaksi Operasional
            for ($j = 0; $j < $day['ops_count']; $j++) {
                $tanggalOps = $currentDate->copy()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $isPemasukan = $faker->boolean(30);
                
                if ($isPemasukan) {
                    TransaksiOperasional::create([
                        'perusahaan_id' => $perusahaanId,
                        'user_id' => 1,
                        'tanggal' => $tanggalOps,
                        'operasional' => 'pemasukan',
                        'kategori' => KategoriOperasional::BAYAR_HUTANG,
                        'nominal' => $faker->numberBetween(100000, 1000000),
                        'pihak_type' => 'Supir',
                        'pihak_id' => $faker->randomElement($supirIds),
                        'keterangan' => 'Bayar Hutang Supir (' . $currentDate->format('d/m') . ')',
                    ]);
                } else {
                    $kat = $faker->randomElement([KategoriOperasional::UANG_JALAN, KategoriOperasional::BAHAN_BAKAR, KategoriOperasional::LAIN_LAIN]);
                    $ket = ($kat == KategoriOperasional::LAIN_LAIN) ? 'Belanja Kasir (' . $currentDate->format('d/m') . ')' : 'Ops ' . $kat->label();
                    
                    TransaksiOperasional::create([
                        'perusahaan_id' => $perusahaanId,
                        'user_id' => 1,
                        'tanggal' => $tanggalOps,
                        'operasional' => 'pengeluaran',
                        'kategori' => $kat,
                        'nominal' => $faker->numberBetween(50000, 500000),
                        'keterangan' => $ket,
                    ]);
                }
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\n[BERHASIL] Simulasi 3 hari selesai: H-2 (50), Kemarin (100), Hari Ini (10).");
    }
}
