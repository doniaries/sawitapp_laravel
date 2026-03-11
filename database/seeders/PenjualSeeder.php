<?php

namespace Database\Seeders;

use App\Models\Penjual;
use Illuminate\Database\Seeder;

class PenjualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds initial penjual data with default null values for non-essential fields
     */
    public function run(): void
    {
        // Define array of penjual data
        $penjuals = [
            [
                'id' => 1,
                'nama' => 'FURQON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 2,
                'nama' => 'EPI',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 3,
                'nama' => 'ANDES',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 4,
                'nama' => 'LOPON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 5,
                'nama' => 'HERMAN',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 6,
                'nama' => 'SIIT',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 7,
                'nama' => 'ETI SUSANA',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 1500000
            ],
            [
                'id' => 8,
                'nama' => 'SEBON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 9,
                'nama' => 'SISKA',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 10,
                'nama' => 'JEKI',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 11,
                'nama' => 'AGUS',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 12,
                'nama' => 'JOKO',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 13,
                'nama' => 'SUKARMIN',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 1500000
            ],
            [
                'id' => 14,
                'nama' => 'DITEG',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 15,
                'nama' => 'KELOMPOK',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 16,
                'nama' => 'UCOK',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'id' => 17,
                'nama' => 'ARI WAHYU',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
        ];

        // Loop through and create each penjual record
        foreach ($penjuals as $penjual) {
            Penjual::create($penjual);
        }
    }
}
