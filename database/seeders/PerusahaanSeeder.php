<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): ?Perusahaan
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');



        // Clear existing records
        // DB::table('users')->where('perusahaan_id', '!=', null)->delete();
        // Perusahaan::truncate();

        // Create the main perusahaan
        $perusahaan1 = Perusahaan::firstOrCreate(
            ['name' => 'CV SUCCESS MANDIRI'],
            [
                'alamat' => 'Dusun Sungai Moran Nagari Kamang',
                'telepon' => '+62 823-8921-9670',
                'pimpinan' => 'Yondra',
                'npwp' => '12.345.678.9-123.000',
                'is_active' => true,
                'sisa_saldo_kemarin' => 0,
                'tanggal_sisa_saldo' => now(),
                'sudah_diproses' => false,
            ]
        );

        // Create the second perusahaan
        $perusahaan2 = Perusahaan::firstOrCreate(
            ['name' => 'PT Andala Integrasi Global'],
            [
                'alamat' => 'Jl. Lintas Sumatera No. 45',
                'telepon' => '+62 812-3456-7890',
                'pimpinan' => 'Yondra',
                'npwp' => '98.765.432.1-456.000',
                'is_active' => true,
                'sisa_saldo_kemarin' => 0,
                'tanggal_sisa_saldo' => now(),
                'sudah_diproses' => false,
            ]
        );

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return $perusahaan1;
    }
}
