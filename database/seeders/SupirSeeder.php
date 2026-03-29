<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supir;

class SupirSeeder extends Seeder
{
    public function run(): void
    {
        $perusahaans = \App\Models\Perusahaan::all();

        if ($perusahaans->isEmpty()) {
            $this->command->error("Tidak ada perusahaan ditemukan. Jalankan PerusahaanSeeder terlebih dahulu.");
            return;
        }

        foreach ($perusahaans as $perusahaan) {
            $this->command->info("Menyiapkan 5 supir untuk: " . $perusahaan->name);

            $names = [
                'FURQON', 'EPI', 'ANDES', 'ICAN', 'HERMAN'
            ];

            foreach ($names as $index => $name) {
                Supir::updateOrCreate(
                    [
                        'perusahaan_id' => $perusahaan->id,
                        'nama' => $name . " (" . $perusahaan->name . ")",
                    ],
                    [
                        'telepon' => '0812' . rand(10000000, 99999999),
                        'alamat' => 'Alamat Supir ' . ($index + 1),
                        'hutang' => rand(100000, 1000000),
                    ]
                );
            }
        }

        $this->command->info("Supir Seeder berhasil dijalankan (5 supir per perusahaan).");
    }
}
