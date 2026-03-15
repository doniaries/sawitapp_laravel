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
        // Seed perusahaan & shield terlebih dahulu
        $this->call([
            PerusahaanSeeder::class,
            ShieldSeeder::class,
        ]);

        $perusahaans = Perusahaan::all();
        $perusahaanPertama = $perusahaans->first();

        // Buat Super Admin tied ke perusahaan pertama
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'perusahaan_id' => $perusahaanPertama?->id,
            ]
        );

        // Assign role super_admin di setiap perusahaan
        // agar semua perusahaan bisa diakses
        foreach ($perusahaans as $perusahaan) {
            setPermissionsTeamId($perusahaan->id);
            $superadmin->assignRole('super_admin');
        }

        // Daftarkan superadmin ke semua perusahaan via pivot (untuk HasTenants Filament)
        $superadmin->perusahaans()->sync($perusahaans->pluck('id'));

        // Reset ke context perusahaan pertama
        setPermissionsTeamId($perusahaanPertama->id);

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
