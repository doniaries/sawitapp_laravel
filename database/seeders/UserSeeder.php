<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $perusahaan1 = Perusahaan::where('name', 'CV SUCCESS MANDIRI')->first();
        $perusahaan2 = Perusahaan::where('name', 'PT Andala Integrasi Global')->first();

        // ========== Perusahaan 1: CV SUCCESS MANDIRI ==========
        if ($perusahaan1) {
            setPermissionsTeamId($perusahaan1->id);

            $yondra = User::firstOrCreate(
                ['email' => 'yondra@gmail.com'],
                [
                    'name' => 'Yondra',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'perusahaan_id' => $perusahaan1->id,
                ]
            );
            $yondra->syncRoles(['admin']);
            $yondra->perusahaans()->syncWithoutDetaching([$perusahaan1->id]);

            $kasir = User::firstOrCreate(
                ['email' => 'kasir1@gmail.com'],
                [
                    'name' => 'Taufik',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'perusahaan_id' => $perusahaan1->id,
                ]
            );
            $kasir->syncRoles(['kasir']);
            $kasir->perusahaans()->syncWithoutDetaching([$perusahaan1->id]);
        }

        // ========== Perusahaan 2: PT Andala Integrasi Global ==========
        if ($perusahaan2) {
            setPermissionsTeamId($perusahaan2->id);
            $yondra = User::firstOrCreate(
                ['email' => 'yondra2@gmail.com'],
                [
                    'name' => 'Yondra 2',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'perusahaan_id' => $perusahaan2->id,
                ]
            );
            $yondra->syncRoles(['admin']);
            $yondra->perusahaans()->syncWithoutDetaching([$perusahaan2->id]);
            $wendy = User::firstOrCreate(
                ['email' => 'kasir2@gmail.com'],
                [
                    'name' => 'Kasir 2',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'perusahaan_id' => $perusahaan2->id,
                ]
            );
            $wendy->syncRoles(['kasir']);
            $wendy->perusahaans()->syncWithoutDetaching([$perusahaan2->id]);
        }
    }
}
