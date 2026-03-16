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

        // ========== Perusahaan 1: CV SUCCESS MANDIRI & Perusahaan 2: PT Andala Integrasi Global ==========
        if ($perusahaan1 && $perusahaan2) {
            // Utama: Yondra sebagai Admin di kedua perusahaan
            $yondra = User::firstOrCreate(
                ['email' => 'yondra@gmail.com'],
                [
                    'name' => 'Yondra',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'perusahaan_id' => $perusahaan1->id, // Fallback
                ]
            );

            // Admin: Akun Admin secara otomatis terhubung ke SEMUA perusahaan (Best Practice)
            $allPerusahaanIds = Perusahaan::pluck('id')->toArray();
            $yondra->perusahaans()->syncWithoutDetaching($allPerusahaanIds);
 
            // Berikan role admin di SEMUA tim/perusahaan
            foreach ($allPerusahaanIds as $pId) {
                setPermissionsTeamId($pId);
                $yondra->syncRoles(['admin']);
            }

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

            // Kasir 2 di Perusahaan 2
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
            setPermissionsTeamId($perusahaan2->id);
            $wendy->syncRoles(['kasir']);
            $wendy->perusahaans()->syncWithoutDetaching([$perusahaan2->id]);
        }
    }
}
