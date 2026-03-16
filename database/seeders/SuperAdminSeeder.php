<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perusahaans = Perusahaan::all();
        $perusahaanPertama = $perusahaans->first();

        if (!$perusahaanPertama) {
            $this->command->warn('Tidak ada data Perusahaan. Harap jalankan PerusahaanSeeder terlebih dahulu.');
            return;
        }

        // Buat Super Admin tied ke perusahaan pertama
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'perusahaan_id' => $perusahaanPertama->id,
            ]
        );

        // Assign role super_admin di setiap perusahaan (Multi-tenancy Shield)
        foreach ($perusahaans as $perusahaan) {
            setPermissionsTeamId($perusahaan->id);
            $superadmin->assignRole('super_admin');
        }

        // Daftarkan superadmin ke semua perusahaan via pivot (untuk HasTenants Filament)
        $superadmin->perusahaans()->sync($perusahaans->pluck('id'));

        // Reset ke context perusahaan pertama
        setPermissionsTeamId($perusahaanPertama->id);

        $this->command->info('Super Admin berhasil dibuat/diperbarui.');
    }
}
