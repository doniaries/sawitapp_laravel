<?php

namespace Database\Factories;

use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerusahaanFactory extends Factory
{
    protected $model = Perusahaan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'alamat' => $this->faker->address,
            'telepon' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
        ];
    }
}
