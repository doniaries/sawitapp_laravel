<?php

namespace Database\Seeders;

use App\Models\Pekerja;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class PekerjaSeeder extends Seeder
{
    public function run(): void
    {
        $perusahaan1 = Perusahaan::where('name', 'CV SUCCESS MANDIRI')->first();
        $perusahaan2 = Perusahaan::where('name', 'PT Andalas Integrasi Global')->first();

        if ($perusahaan1) {
            $pekerjaCV = [
                ['nama' => 'Budi Santoso', 'pendapatan' => 3500000, 'hutang' => 500000],
                ['nama' => 'Agus Setiawan', 'pendapatan' => 3200000, 'hutang' => 0],
                ['nama' => 'Siti Aminah', 'pendapatan' => 3000000, 'hutang' => 200000],
                ['nama' => 'Dedi Kurniawan', 'pendapatan' => 3800000, 'hutang' => 1000000],
            ];

            foreach ($pekerjaCV as $data) {
                Pekerja::create(array_merge($data, [
                    'perusahaan_id' => $perusahaan1->id,
                    'alamat' => 'Alamat CV SUCCESS MANDIRI',
                    'telepon' => '0812' . rand(10000000, 99999999),
                ]));
            }
        }

        if ($perusahaan2) {
            $pekerjaPT = [
                ['nama' => 'Randi Pratama', 'pendapatan' => 4500000, 'hutang' => 0],
                ['nama' => 'Maya Sari', 'pendapatan' => 4200000, 'hutang' => 300000],
                ['nama' => 'Hendra Wijaya', 'pendapatan' => 4000000, 'hutang' => 150000],
                ['nama' => 'Dewi Lestari', 'pendapatan' => 4800000, 'hutang' => 800000],
            ];

            foreach ($pekerjaPT as $data) {
                Pekerja::create(array_merge($data, [
                    'perusahaan_id' => $perusahaan2->id,
                    'alamat' => 'Alamat PT Andala Integrasi Global',
                    'telepon' => '0821' . rand(10000000, 99999999),
                ]));
            }
        }
    }
}
