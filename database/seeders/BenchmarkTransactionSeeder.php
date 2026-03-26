<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{Perusahaan, Penjual, Supir, TransaksiDo, TransaksiOperasional};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class BenchmarkTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // Target perusahaan ID 1 (Sukses Mandiri)
        $perusahaan = Perusahaan::find(1) ?? Perusahaan::first();
        if (!$perusahaan) {
            $this->command->error('Perusahaan tidak ditemukan.');
            return;
        }

        $perusahaanId = $perusahaan->id;
        
        // Bersihkan data TransaksiDo sebelumnya untuk perusahaan ini agar mulai dari nol
        // Kita gunakan truncate jika ingin benar-benar bersih, tapi forceDelete cukup
        TransaksiDo::where('perusahaan_id', $perusahaanId)->forceDelete();
        \App\Models\JurnalKeuangan::where('perusahaan_id', $perusahaanId)
            ->whereIn('sumber_transaksi', ['DO', 'Operasional'])
            ->forceDelete();
        
        TransaksiOperasional::where('perusahaan_id', $perusahaanId)->forceDelete();
        
        $this->command->info('Memulai seeding 1000 transaksi menggunakan Eloquent (untuk mengetes performa observer)...');

        // Ambil beberapa penjual dan supir yang ada
        $penjualIds = Penjual::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
        $supirIds = Supir::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();

        if (empty($penjualIds)) {
            $penjual = Penjual::create(['nama' => 'Penjual Benchmark', 'perusahaan_id' => $perusahaanId]);
            $penjualIds = [$penjual->id];
        }

        if (empty($supirIds)) {
            $supir = Supir::create(['nama' => 'Supir Benchmark', 'perusahaan_id' => $perusahaanId]);
            $supirIds = [$supir->id];
        }

        $totalTransactions = 1000;
        $now = Carbon::now();

        $progressBar = $this->command->getOutput()->createProgressBar($totalTransactions);
        $progressBar->start();

        for ($i = 0; $i < $totalTransactions; $i++) {
            $tonase = $faker->numberBetween(500, 5000);
            $harga = $faker->randomElement([3500, 3510, 3520, 3450]);
            
            try {
                TransaksiDo::create([
                    'perusahaan_id' => $perusahaanId,
                    'tanggal' => $now->copy()->startOfDay()->addSeconds($i * 86), // Sebar dalam 1 hari (86400/1000)
                    'penjual_id' => $faker->randomElement($penjualIds),
                    'supir_id' => $faker->randomElement($supirIds),
                    'no_polisi' => $faker->bothify('BK #### ??'),
                    'tonase' => $tonase,
                    'harga_satuan' => $harga,
                    'upah_bongkar' => 0,
                    'biaya_lain' => 0,
                    'pembayaran_hutang' => 0,
                    'cara_bayar' => $faker->randomElement(['tunai', 'transfer']),
                    'keterangan_pembayaran' => 'Benchmark record ' . ($i + 1),
                ]);
            } catch (\Exception $e) {
                $this->command->error("\nError at $i: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->info("\n1000 transaksi DO berhasil disuntikkan.");

        // SEED OPERASIONAL (2000 Data)
        $this->command->info('Memulai seeding 2000 transaksi OPERASIONAL (Campuran Pihak & Hutang)...');
        $totalOp = 2000;
        $progressBarOp = $this->command->getOutput()->createProgressBar($totalOp);
        $progressBarOp->start();

        $pekerjaIds = \App\Models\Pekerja::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
        $allPihak = [];
        
        foreach ($supirIds as $id) $allPihak[] = ['type' => \App\Models\Supir::class, 'id' => $id];
        foreach ($pekerjaIds as $id) $allPihak[] = ['type' => \App\Models\Pekerja::class, 'id' => $id];
        foreach ($penjualIds as $id) $allPihak[] = ['type' => \App\Models\Penjual::class, 'id' => $id];

        for ($i = 0; $i < $totalOp; $i++) {
            try {
                $pihak = $faker->randomElement($allPihak);
                $kategori = $faker->randomElement(\App\Enums\KategoriOperasional::cases());
                
                // Berikan bobot lebih ke Pemasukan agar saldo tidak terlalu minus
                if ($i % 3 === 0) {
                    $kategori = $faker->randomElement([\App\Enums\KategoriOperasional::TAMBAH_SALDO, \App\Enums\KategoriOperasional::BAYAR_HUTANG]);
                }

                TransaksiOperasional::create([
                    'perusahaan_id' => $perusahaanId,
                    'tanggal' => $now->copy()->subDays(7)->addSeconds($i * 300), // Sebar dalam beberapa hari terakhir
                    'pihak_type' => $pihak['type'],
                    'pihak_id' => $pihak['id'],
                    'kategori' => $kategori,
                    'nominal' => $faker->numberBetween(50000, 2000000),
                    'keterangan' => 'Benchmark Operasional ' . ($i + 1) . ' - ' . $kategori->label(),
                ]);
            } catch (\Exception $e) {
                // Log silent untuk seeder
            }
            $progressBarOp->advance();
        }

        $progressBarOp->finish();
        $this->command->info("\n2000 transaksi Operasional berhasil disuntikkan.");
    }
}
