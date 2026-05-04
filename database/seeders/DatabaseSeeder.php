<?php

namespace Database\Seeders;

use Database\Seeders\BenchmarkTransactionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Proteksi: Cek apakah sudah ada data transaksi
        if (\App\Models\TransaksiDo::exists()) {
            if (!$this->command->confirm('PERINGATAN: Sudah ada data transaksi di database. Menjalankan seeder mungkin akan menambah data master duplikat atau mengganggu integritas data. Lanjutkan?', false)) {
                $this->command->info('Seeding dibatalkan.');
                return;
            }
        }

        // Seed perusahaan, shield, dan super admin

        $this->call([
            PerusahaanSeeder::class,
            ShieldSeeder::class,
            SuperAdminSeeder::class,
        ]);


        // Disable observers during seeding to avoid tenant scoping/validation issues
        // Observers are now enabled during seeding for better data integrity

        $this->call([
            UserSeeder::class,
            PenjualSeeder::class,
            SupirSeeder::class,
            PekerjaSeeder::class,
        ]);

        // Hanya jalankan seeder transaksi jika TIDAK di produksi
        if (!app()->environment('production')) {
            $this->call([
                SimulasiDataSeeder::class,
                // OperasionalSeeder::class,
            ]);
        }

        $this->command->info("Seeders berhasil dijalankan");
    }
}
