<?php

namespace Database\Factories;

use App\Models\Penjual;
use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenjualFactory extends Factory
{
    protected $model = Penjual::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'telepon' => $this->faker->phoneNumber(),
            'alamat' => $this->faker->address(),
            'hutang' => 0,
            'perusahaan_id' => Perusahaan::factory(),
        ];
    }
}
