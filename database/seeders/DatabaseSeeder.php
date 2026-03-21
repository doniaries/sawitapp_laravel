<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
            OperasionalSeeder::class,
            SimulationSeeder::class,
        ]);
        $this->command->info("Seeders berhasil dijalankan");
    }
}
