<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supir;

class SupirSeeder extends Seeder
{
    public function run(): void
    {
        $supirs = [
            [
                'id' => 1,
                'nama' => 'FURQON',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 2,
                'nama' => 'FURQONS',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 3,
                'nama' => 'EPI',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 4,
                'nama' => 'ANDES',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 5,
                'nama' => 'ICAN',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 6,
                'nama' => 'HERMAN',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 7,
                'nama' => 'SIIT',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 8,
                'nama' => 'NARO',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 9,
                'nama' => 'AGUS',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 10,
                'nama' => 'JEKI',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 11,
                'nama' => 'JOKO',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 12,
                'nama' => 'WILCO',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 13,
                'nama' => 'KOMBET',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 14,
                'nama' => 'DODY',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 15,
                'nama' => 'KELOMPOK',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 16,
                'nama' => 'AGUNG',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 17,
                'nama' => 'UCOK',
                'telepon' => '',
                'alamat' => '',
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 18,
                'nama' => 'ARI WAHYU',
                'telepon' => '',
                'alamat' => '',
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
            [
                'id' => 19,
                'nama' => 'EKO',
                'telepon' => null,
                'alamat' => null,
                'hutang' => null,
                'riwayat_bayar' => null,
            ],
        ];

        foreach ($supirs as $supir) {
            Supir::create($supir);
        }
    }
}