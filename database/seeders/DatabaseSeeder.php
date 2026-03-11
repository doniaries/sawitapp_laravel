<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Super Admin
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ShieldSeeder::class,
            UserSeeder::class,
            PerusahaanSeeder::class,
            PenjualSeeder::class,
            SupirSeeder::class,
            OperasionalSeeder::class,
            TransaksiDoSeeder::class,
        ]);

        // Assign role super_admin setelah Shield seeder berjalan
        $superadmin->syncRoles(['super_admin']);
    }
}
