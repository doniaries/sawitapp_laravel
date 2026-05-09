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
        $totalData = 400;
        $startOfMonth = $now->copy()->startOfMonth();
        $endMonth = $now->copy();

        $this->command->info('Menyuntikkan modal 500 Juta (4x)...');
        for ($k = 1; $k <= 4; $k++) {
            $tglInjeksi = Carbon::createFromTimestamp(rand($startOfMonth->timestamp, $endMonth->timestamp));
            JurnalKeuangan::create([
                'perusahaan_id' => $perusahaanId,
                'tanggal' => $tglInjeksi,
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'Saldo',
                'sub_kategori' => 'Tambah Saldo',
                'nominal' => 500000000,
                'cara_pembayaran' => 'transfer',
                'keterangan' => 'Injeksi Modal Strategis #' . $k,
                'mempengaruhi_kas' => true,
                'sumber_transaksi' => 'Manual',
                'referensi_id' => 0,
                'nomor_referensi' => 'BIG-INJ-' . $k,
            ]);
        }

        $this->command->info('Memulai simulasi 400 transaksi (Harga: 3500-3600, Berat: 1000-2000)...');
        $progressBar = $this->command->getOutput()->createProgressBar($totalData);
        $progressBar->start();


        for ($i = 0; $i < $totalData; $i++) {
            $tanggal = $startOfMonth->copy()->addDays(rand(0, $now->day - 1))->addHours(rand(8, 17));
            $roll = rand(1, 100);

            if ($roll <= 70) { 
                $penjual = $penjuals->random();
                $tonase = $faker->numberBetween(1000, 2000); 
                $harga = $faker->numberBetween(3500, 3600); 
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
                    'nomor' => 'DO-' . $tanggal->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'tanggal' => $tanggal,
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
                    'sisa_hutang_penjual' => $sisaHutangReal - $bayarHutang,
                    'sisa_bayar' => $subTotal - $bayarHutang - $upahBongkar - $biayaLain,
                    'cara_bayar' => 'transfer',
                    'nominal_tunai' => 0,
                    'keterangan_pembayaran' => 'DO Simulasi ' . $tanggal->format('d/m'),
                ]);

                $penjual->hutang = $sisaHutangReal - $bayarHutang;

                if ($faker->boolean(30)) {
                    $supirId = $faker->randomElement($supirIds);
                    TransaksiOperasional::create([
                        'perusahaan_id' => $perusahaanId,
                        'user_id' => 1,
                        'tanggal' => $tanggal,
                        'operasional' => 'pemasukan',
                        'kategori' => KategoriOperasional::BAYAR_HUTANG,
                        'nominal' => $faker->numberBetween(100000, 500000),
                        'pihak_type' => 'Supir',
                        'pihak_id' => $supirId,
                        'keterangan' => 'Bayar Hutang Supir (Simulasi)',
                    ]);
                }
            } 
            else {
                if ($faker->boolean(50)) {
                    TransaksiOperasional::create([
                        'perusahaan_id' => $perusahaanId,
                        'user_id' => 1,
                        'tanggal' => $tanggal,
                        'operasional' => 'pengeluaran',
                        'kategori' => KategoriOperasional::LAIN_LAIN,
                        'nominal' => $faker->numberBetween(50000, 100000),
                        'keterangan' => 'Belanja Kasir (Simulasi)',
                    ]);
                } else {
                    $kat = $faker->randomElement($kategoriOps);
                    TransaksiOperasional::create([
                        'perusahaan_id' => $perusahaanId,
                        'user_id' => 1,
                        'tanggal' => $tanggal,
                        'operasional' => $kat->getJenisOperasional(),
                        'kategori' => $kat,
                        'nominal' => $faker->numberBetween(50000, 1000000),
                        'keterangan' => 'Operasional ' . $kat->label(),
                    ]);
                }
            }

            $progressBar->advance();
        }

        // TAMBAHAN: DATA KHUSUS HARI INI (PADAT)
        $this->command->info("\nMenambahkan data khusus untuk hari ini (" . $now->format('d/m/Y') . ")...");
        
        // 1. 10 Transaksi DO Hari Ini
        for ($j = 0; $j < 10; $j++) {
            $tanggalToday = $now->copy()->subMinutes(rand(0, 480)); 
            $penjual = $penjuals->random();
            $tonase = $faker->numberBetween(1200, 2500);
            $harga = 3600;
            $subTotal = $tonase * $harga;
            
            $upahBongkar = $faker->numberBetween(50000, 100000);
            $biayaLain = $faker->numberBetween(100000, 300000);
            $bayarHutang = ($penjual->hutang > 200000) ? $faker->numberBetween(200000, 500000) : 0;

            TransaksiDo::create([
                'perusahaan_id' => $perusahaanId,
                'user_id' => 1,
                'nomor' => 'DO-' . $tanggalToday->format('Ym') . '-TODAY-' . ($j+1),
                'tanggal' => $tanggalToday,
                'penjual_id' => $penjual->id,
                'supir_id' => $faker->randomElement($supirIds),
                'no_polisi' => 'BA ' . $faker->numberBetween(1000, 9999) . ' ' . $faker->lexify('??'),
                'tonase' => $tonase,
                'harga_satuan' => $harga,
                'sub_total' => $subTotal,
                'upah_bongkar' => $upahBongkar,
                'biaya_lain' => $biayaLain,
                'hutang_awal' => $penjual->hutang,
                'pembayaran_hutang' => $bayarHutang,
                'sisa_hutang_penjual' => max(0, $penjual->hutang - $bayarHutang),
                'sisa_bayar' => max(0, $subTotal - $bayarHutang - $upahBongkar - $biayaLain),
                'cara_bayar' => $faker->randomElement(['tunai', 'transfer']),
                'nominal_tunai' => 0,
                'keterangan_pembayaran' => 'DO AKTIF HARI INI',
            ]);
        }

        // 2. 15 Transaksi Operasional Hari Ini (Banyak)
        for ($k = 0; $k < 15; $k++) {
            $tanggalToday = $now->copy()->subMinutes(rand(0, 500));
            $isPemasukan = $faker->boolean(30);
            
            if ($isPemasukan) {
                // Bayar Hutang Supir
                TransaksiOperasional::create([
                    'perusahaan_id' => $perusahaanId,
                    'user_id' => 1,
                    'tanggal' => $tanggalToday,
                    'operasional' => 'pemasukan',
                    'kategori' => KategoriOperasional::BAYAR_HUTANG,
                    'nominal' => $faker->numberBetween(200000, 1000000),
                    'pihak_type' => 'Supir',
                    'pihak_id' => $faker->randomElement($supirIds),
                    'keterangan' => 'Bayar Hutang Supir Hari Ini',
                ]);
            } else {
                // Pengeluaran Beragam
                $kat = $faker->randomElement([KategoriOperasional::UANG_JALAN, KategoriOperasional::BAHAN_BAKAR, KategoriOperasional::LAIN_LAIN]);
                $ket = ($kat == KategoriOperasional::LAIN_LAIN) ? 'Belanja Kasir Hari Ini' : 'Ops ' . $kat->label();
                
                TransaksiOperasional::create([
                    'perusahaan_id' => $perusahaanId,
                    'user_id' => 1,
                    'tanggal' => $tanggalToday,
                    'operasional' => 'pengeluaran',
                    'kategori' => $kat,
                    'nominal' => $faker->numberBetween(50000, 300000),
                    'keterangan' => $ket,
                ]);
            }
        }

        // 3. 3 Transaksi Tambah Saldo Hari Ini
        for ($l = 0; $l < 3; $l++) {
            TambahSaldo::create([
                'perusahaan_id' => $perusahaanId,
                'user_id' => 1,
                'tanggal' => $now->copy()->subHours(rand(1, 5)),
                'nominal' => $faker->randomElement([5000000, 10000000, 20000000]),
                'keterangan' => 'Tambah Saldo Modal Hari Ini',
            ]);
        }

        $progressBar->finish();
        $this->command->info("\n[BERHASIL] 400 data bulanan + 28 data khusus hari ini (10 DO, 15 Ops, 3 Saldo) telah dimasukkan.");
    }
}
