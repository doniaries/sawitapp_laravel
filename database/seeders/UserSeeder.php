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
        $perusahaan2 = Perusahaan::where('name', 'PT Andalas Integrasi Global')->first();

        // ========== Perusahaan 1: CV SUCCESS MANDIRI ==========
        if ($perusahaan1) {
            // Yondra sebagai Pimpinan di semua perusahaan
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

            // Akses ke SEMUA perusahaan
            $allPerusahaanIds = Perusahaan::pluck('id')->toArray();
            $yondra->perusahaans()->syncWithoutDetaching($allPerusahaanIds);
 
            // Role pimpinan secara GLOBAL
            setPermissionsTeamId(null);
            $yondra->syncRoles(['pimpinan']);

            // Taufik: Kasir di Perusahaan 1 saja
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
            setPermissionsTeamId($perusahaan1->id);
            $kasir->syncRoles(['kasir']);
            $kasir->perusahaans()->syncWithoutDetaching([$perusahaan1->id]);
        }

        // ========== Admin Kedua: Wendi ==========
        $wendi = User::firstOrCreate(
            ['email' => 'wendi@gmail.com'],
            [
                'name' => 'Wendi',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'perusahaan_id' => $perusahaan1->id ?? null,
            ]
        );
        $allPerusahaanIds = Perusahaan::all()->pluck('id');
        $wendi->perusahaans()->syncWithoutDetaching($allPerusahaanIds);
        setPermissionsTeamId(null);
        $wendi->syncRoles(['admin']);
    }
}
