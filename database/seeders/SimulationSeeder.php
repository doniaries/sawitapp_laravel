<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiDo;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\MutasiHutang;
use App\Models\Perusahaan;
use App\Models\JurnalKeuangan;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        $perusahaans = Perusahaan::all();

        if ($perusahaans->isEmpty()) {
            $perusahaans = collect([Perusahaan::create([
                'name' => 'SUKSES MANDIRI',
                'alamat' => 'Jl. Lintas Sumatera',
                'telepon' => '08123456789',
                'saldo' => 800000000,
            ])]);
        }

        foreach ($perusahaans as $perusahaan) {
            $this->command->info("Starting DO simulation for: {$perusahaan->name}");
            $perusahaanId = $perusahaan->id;

            // Cleanup only DO related data
            TransaksiDo::where('perusahaan_id', $perusahaanId)->forceDelete();
            
            $penjualIds = Penjual::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();
            $supirIds = Supir::where('perusahaan_id', $perusahaanId)->pluck('id')->toArray();

            if (empty($penjualIds) || empty($supirIds)) {
                $this->command->warn("Missing Penjual or Supir for {$perusahaan->name}. Skipping...");
                continue;
            }

            // observers enabled for accuracy
            // TransaksiDo::unsetEventDispatcher();

            // Generate data for the last 6 months
            $startDate = now()->subMonths(6)->startOfMonth();
            $currentDate = clone $startDate;

            while ($currentDate < now()->startOfDay()) {
                $count = rand(15, 30);
                $daysInMonth = $currentDate->daysInMonth;

                for ($i = 1; $i <= $count; $i++) {
                    $targetDay = rand(1, $daysInMonth);
                    $tanggal = (clone $currentDate)->setDay($targetDay)->setHour(rand(8, 17))->setMinute(rand(0, 59));
                    if ($tanggal >= now()->startOfDay()) continue;

                    $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
                }
                $currentDate->addMonth();
            }

            // Yesterday & Today
            for ($i = 1; $i <= 5; $i++) {
                $tanggal = now()->subDay()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
            }

            for ($i = 1; $i <= 3; $i++) {
                $maxMinutes = max(1, now()->diffInMinutes(now()->startOfDay()));
                $tanggal = now()->startOfDay()->addMinutes(rand(0, $maxMinutes));
                $this->createMonthlyTransaction($tanggal, $i, $penjualIds, $supirIds, $perusahaanId);
            }
        }

        $this->command->info("Simulasi Jual Beli Sawit berhasil dibuat!");
    }

    private function createMonthlyTransaction($tanggal, $index, $penjualIds, $supirIds, $perusahaanId)
    {
        $tonase = rand(1000, 10000);
        $harga = rand(3000, 3500);
        $subTotal = $tonase * $harga;
        $caraBayar = ['tunai', 'transfer', 'cair di luar', 'belum dibayar'][rand(0, 3)];

        $penjualId = $penjualIds[array_rand($penjualIds)];
        $supirId = $supirIds[array_rand($supirIds)];
        $nomor = 'DO-' . $perusahaanId . '-' . $tanggal->format('Ymd') . '-' . Str::padLeft($index, 4, '0');

        // Observer will handle JurnalKeuangan and MutasiHutang
        $transaksi = TransaksiDo::create([
            'nomor' => $nomor,
            'tanggal' => $tanggal,
            'penjual_id' => $penjualId,
            'supir_id' => $supirId,
            'no_polisi' => 'B ' . rand(1000, 9999) . ' XYZ',
            'tonase' => $tonase,
            'harga_satuan' => $harga,
            'sub_total' => $subTotal,
            'upah_bongkar' => 50000,
            'biaya_lain' => 0,
            'sisa_bayar' => $subTotal,
            'pembayaran_hutang' => 0, // Simplified for seeder to avoid balance/debt complexity
            'cara_bayar' => $caraBayar,
            'perusahaan_id' => $perusahaanId,
        ]);
    }
}
