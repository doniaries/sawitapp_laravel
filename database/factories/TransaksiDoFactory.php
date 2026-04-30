<?php

namespace Database\Factories;

use App\Models\Penjual;
use App\Models\Perusahaan;
use App\Models\Supir;
use App\Models\TransaksiDo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransaksiDoFactory extends Factory
{
    protected $model = TransaksiDo::class;

    public function definition(): array
    {
        $tonase = $this->faker->numberBetween(1000, 5000);
        $hargaSatuan = $this->faker->numberBetween(2000, 4000);
        $subTotal = $tonase * $hargaSatuan;
        $upahBongkar = 100000;
        $biayaLain = 0;
        $sisaBayar = $subTotal - $upahBongkar - $biayaLain;

        return [
            'perusahaan_id' => Perusahaan::factory(),
            'user_id' => User::factory(),
            'nomor' => 'DO-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'tanggal' => now(),
            'penjual_id' => Penjual::factory(),
            'supir_id' => Supir::factory(),
            'no_polisi' => 'B ' . $this->faker->numberBetween(1000, 9999) . ' ABC',
            'tonase' => $tonase,
            'harga_satuan' => $hargaSatuan,
            'sub_total' => $subTotal,
            'upah_bongkar' => $upahBongkar,
            'biaya_lain' => $biayaLain,
            'cara_bayar' => 'tunai',
            'nominal_tunai' => 0,
            'sisa_bayar' => $sisaBayar,
            'hutang_awal' => 0,
            'pembayaran_hutang' => 0,
            'sisa_hutang_penjual' => 0,
        ];
    }
}
