<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Penjual;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TransaksiDoSeeder;
use Database\Seeders\OperasionalSeeder;
use Database\Seeders\PerusahaanSeeder;
use Database\Seeders\PenjualSeeder;
use Database\Seeders\SupirSeeder; // Add this line
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Super Admin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Buat Admin
        $superadmin = User::create([
            'name' => 'Yondra',
            'email' => 'yondra@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Buat Super Admin
        $superadmin = User::create([
            'name' => 'Wendy',
            'email' => 'wendy@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Buat Kasir
        $kasir = User::create([
            'id' => 4,
            'name' => 'Kasir 1',
            'email' => 'kasir1@gmail.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->call([
            // PerusahaanSeeder::class,
            // PenjualSeeder::class,
            // SupirSeeder::class,
            // OperasionalSeeder::class,
            // TransaksiDoSeeder::class,
        ]);

        // Run PerusahaanSeeder and get the created perusahaan
        $perusahaan = app(PerusahaanSeeder::class)->run();

        // Update kasir dengan perusahaan_id
        $kasir->update([
            'perusahaan_id' => $perusahaan->id
        ]);
    }
}
