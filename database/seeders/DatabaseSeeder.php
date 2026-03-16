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
        \App\Models\TransaksiDo::unsetEventDispatcher();
        \App\Models\JurnalKeuangan::unsetEventDispatcher();
        \App\Models\TransaksiOperasional::unsetEventDispatcher();

        $this->call([
            UserSeeder::class,
            PenjualSeeder::class,
            SupirSeeder::class,
            PekerjaSeeder::class,
            OperasionalSeeder::class,
            SimulationSeeder::class,
        ]);
    }
}
