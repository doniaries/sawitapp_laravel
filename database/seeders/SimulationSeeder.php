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
                $count = rand(2, 5);
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
        $tonase = rand(100, 1000);
        $harga = rand(3000, 3500);
        $subTotal = $tonase * $harga;
        $caraBayar = ['tunai', 'transfer', 'cair di luar', 'belum dibayar'][rand(0, 3)];
        
        $keteranganPembayaran = null;
        if ($caraBayar === 'transfer') {
            $keteranganPembayaran = 'Transfer via Bank Mandiri';
        } elseif ($caraBayar === 'cair di luar') {
            $keteranganPembayaran = 'Cair di agen ' . rand(1, 5);
        } elseif ($caraBayar === 'belum dibayar') {
            $keteranganPembayaran = 'Jatuh tempo ' . $tanggal->addDays(7)->format('d/m/Y');
        }

        $penjualId = $penjualIds[array_rand($penjualIds)];
        $supirId = $supirIds[array_rand($supirIds)];
        $nopolList = ['BH 8021 SM', 'BH 8112 MA', 'BH 9090 KT', 'B 1234 ABC', 'B 5678 DEF', 'BH 7777 SS'];
        $noPolisi = $nopolList[array_rand($nopolList)];
        
        $nomor = 'DO-' . $perusahaanId . '-' . $tanggal->format('Ymd') . '-' . Str::padLeft($index, 4, '0');
        
        // Ensure uniqueness if seeder is run multiple times or has date collisions
        while (TransaksiDo::where('nomor', $nomor)->exists()) {
            $nomor = 'DO-' . $perusahaanId . '-' . $tanggal->format('Ymd') . '-' . Str::padLeft($index++, 4, '0') . Str::random(2);
        }

        // Observer will handle JurnalKeuangan and MutasiHutang
        $transaksi = TransaksiDo::create([
            'nomor' => $nomor,
            'tanggal' => $tanggal,
            'penjual_id' => $penjualId,
            'supir_id' => $supirId,
            'no_polisi' => $noPolisi,
            'tonase' => $tonase,
            'harga_satuan' => $harga,
            'sub_total' => $subTotal,
            'upah_bongkar' => 50000,
            'biaya_lain' => 0,
            'sisa_bayar' => $subTotal,
            'pembayaran_hutang' => 0, // Simplified for seeder to avoid balance/debt complexity
            'cara_bayar' => $caraBayar,
            'keterangan_pembayaran' => $keteranganPembayaran,
            'perusahaan_id' => $perusahaanId,
        ]);
    }
}
