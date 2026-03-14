<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // First, seed the company (tenant)
        $this->call([
            PerusahaanSeeder::class,
            ShieldSeeder::class,
        ]);

        $perusahaan = \App\Models\Perusahaan::first();

        // Buat Super Admin tied to the perusahaan
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'perusahaan_id' => $perusahaan?->id,
            ]
        );

        // Bebaskan superadmin dari shield (global role)
        setPermissionsTeamId(null);
        $superadmin->syncRoles(['super_admin']);

        // Set back team context for other permissions
        setPermissionsTeamId($perusahaan->id);

        $this->call([
            UserSeeder::class,
            PenjualSeeder::class,
            SupirSeeder::class,
            OperasionalSeeder::class,
            TransaksiDoSeeder::class,
        ]);
    }
}
