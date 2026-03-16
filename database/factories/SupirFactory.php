<?php

namespace Database\Factories;

use App\Models\Supir;
use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupirFactory extends Factory
{
    protected $model = Supir::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->name,
            'alamat' => $this->faker->address,
            'telepon' => $this->faker->phoneNumber,
            'hutang' => 0,
            'perusahaan_id' => Perusahaan::factory(),
        ];
    }
}
