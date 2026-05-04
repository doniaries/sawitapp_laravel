<?php

namespace Database\Seeders;

use App\Models\{Perusahaan, Penjual, Supir, TransaksiDo, TransaksiOperasional, JurnalKeuangan};
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

        if ($penjuals->isEmpty() || empty($supirIds)) {
            $this->command->error('Error: Penjual atau Supir tidak ditemukan.');
            return;
        }

        foreach ($penjuals as $p) {
            $p->update(['hutang' => rand(0, 1) ? rand(2000000, 8000000) : 0]);
        }

        $this->command->info('Menyuntikkan modal 500 Juta (4x)...');
        for ($k = 1; $k <= 4; $k++) {
            $tglInjeksi = Carbon::now()->subDays(rand(0, 28));
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
        $progressBar = $this->command->getOutput()->createProgressBar(400);
        $progressBar->start();

        $kategoriOps = KategoriOperasional::cases();

        for ($i = 1; $i <= 400; $i++) {
            $tanggal = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            $roll = rand(1, 100);

            if ($roll <= 70) { 
                $penjual = $penjuals->random();
                $tonase = $faker->numberBetween(1000, 2000); // 1000kg - 2000kg
                $harga = $faker->numberBetween(3500, 3600); // 3500 - 3600
                $subTotal = $tonase * $harga;
                
                $sisaHutangReal = (float) $penjual->hutang;
                $bayarHutang = ($sisaHutangReal > 200000 && rand(1, 4) === 1) 
                                ? $faker->numberBetween(100000, min($sisaHutangReal, 500000)) 
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
                    'upah_bongkar' => 0,
                    'biaya_lain' => 0,
                    'hutang_awal' => $sisaHutangReal,
                    'pembayaran_hutang' => $bayarHutang,
                    'sisa_hutang_penjual' => $sisaHutangReal - $bayarHutang,
                    'sisa_bayar' => $subTotal - $bayarHutang,
                    'cara_bayar' => 'transfer',
                    'nominal_tunai' => 0,
                    'keterangan_pembayaran' => 'DO Simulasi ' . $tanggal->format('d/m'),
                ]);

                $penjual->hutang = $sisaHutangReal - $bayarHutang;
            } 
            else {
                $kat = $faker->randomElement($kategoriOps);
                TransaksiOperasional::create([
                    'perusahaan_id' => $perusahaanId,
                    'user_id' => 1,
                    'tanggal' => $tanggal,
                    'kategori' => $kat,
                    'nominal' => $faker->numberBetween(50000, 1000000),
                    'keterangan' => 'Operasional ' . $kat->label(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->info("\n[BERHASIL] 400 data simulasi baru (Harga 3.5k - 3.6k) telah dimasukkan.");
    }
}
