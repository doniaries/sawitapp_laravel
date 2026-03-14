<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PabrikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pabriks = [
            [
                'nama' => 'PT. SMP',
                'alamat' => 'Banjar Tengah',
            ],
            [
                'nama' => 'PT. Bina Pratama',
                'alamat' => 'Parit Rantang',
            ],
            [
                'nama' => 'PT. KPPS',
                'alamat' => 'Muaro Takung',
            ],
        ];
        $perusahaan = \App\Models\Perusahaan::first();
        $perusahaanId = $perusahaan?->id;

        foreach ($pabriks as $pabrik) {
            $pabrik['perusahaan_id'] = $perusahaanId;
            \App\Models\Pabrik::create($pabrik);
        }
    }
}
