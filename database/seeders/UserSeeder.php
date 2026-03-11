<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (Yondra)
        $yondra = User::firstOrCreate(
            ['email' => 'yondra@gmail.com'],
            [
                'name' => 'Yondra',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $yondra->syncRoles(['admin']);

        // Admin (Wendy)
        $wendy = User::firstOrCreate(
            ['email' => 'wendy@gmail.com'],
            [
                'name' => 'Wendy',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $wendy->syncRoles(['admin']);

        // Kasir
        $kasir = User::firstOrCreate(
            ['email' => 'kasir1@gmail.com'],
            [
                'name' => 'Taufik',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $kasir->syncRoles(['kasir']);
    }
}
