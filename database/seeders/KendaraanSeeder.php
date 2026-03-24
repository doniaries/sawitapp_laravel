<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KendaraanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supirs = \App\Models\Supir::all();
        $jenisKendaraan = ['FUSO', 'COLT DIESEL', 'L300'];
        $merkKendaraan = ['MITSUBISHI', 'TOYOTA', 'ISUZU'];

        foreach ($supirs as $index => $supir) {
            $jenis = $jenisKendaraan[$index % count($jenisKendaraan)];
            $merk = $merkKendaraan[$index % count($merkKendaraan)];
            
            \App\Models\Kendaraan::create([
                'perusahaan_id' => $supir->perusahaan_id,
                'supir_id' => $supir->id,
                'nama' => "$jenis $merk",
                'no_polisi' => 'BH ' . (8000 + $index) . ' SM',
                'is_maintenance' => false,
            ]);
        }
    }
}
