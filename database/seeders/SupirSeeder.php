<?php

namespace Database\Seeders;

use App\Models\Supir;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SupirSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $perusahaans = Perusahaan::all();

        if ($perusahaans->isEmpty()) {
            return;
        }

        foreach ($perusahaans as $perusahaan) {
            // Tambahkan 100 data per perusahaan
            for ($i = 1; $i <= 100; $i++) {
                Supir::create([
                    'perusahaan_id' => $perusahaan->id,
                    'nama' => strtoupper($faker->name),
                    'alamat' => $faker->address,
                    'telepon' => $faker->phoneNumber,
                    'hutang' => 0,
                ]);
            }
        }
    }
}
