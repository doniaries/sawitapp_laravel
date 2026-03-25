<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{TransaksiOperasional, Perusahaan, JurnalKeuangan, Penjual, Supir, Pekerja, User};
use Illuminate\Support\Facades\DB;
use App\Observers\JurnalKeuanganObserver;
use App\Enums\KategoriOperasional;
use App\Actions\Finance\ProcessDebtPayment;
use Carbon\Carbon;

class OperasionalSeeder extends Seeder
{
    protected $processDebtPayment;

    public function __construct(ProcessDebtPayment $processDebtPayment)
    {
        $this->processDebtPayment = $processDebtPayment;
    }

    public function run(): void
    {
        $perusahaans = Perusahaan::all();
        $admin = User::first() ?: User::factory()->create();

        foreach ($perusahaans as $perusahaan) {
            $this->command->info("Starting Operasional simulation for: {$perusahaan->name}");
            $perusahaanId = $perusahaan->id;

            $this->seedInitialCapital($perusahaan);

            // 1. Generate Daily Transactions for last 6 months
            $startDate = now()->subMonths(6)->startOfMonth();
            $currentDate = clone $startDate;

            while ($currentDate < now()->startOfDay()) {
                $daysInMonth = $currentDate->daysInMonth;
                $expenseCount = rand(5, 10);
                
                for ($i = 0; $i < $expenseCount; $i++) {
                    $tanggal = (clone $currentDate)->setDay(rand(1, $daysInMonth))->setHour(rand(8, 17));
                    if ($tanggal >= now()) continue;
                    $this->seedRandomExpense($perusahaan, $tanggal);
                }
                $currentDate->addMonth();
            }

            // Yesterday & Today
            for ($i = 0; $i < 5; $i++) {
                $this->seedRandomExpense($perusahaan, now()->subDay()->setHour(rand(8, 17)));
            }

            for ($i = 0; $i < 10; $i++) {
                $this->seedRandomExpense($perusahaan, now()->setHour(rand(8, 15)));
            }
        }

        $this->command->info("Simulasi Operasional & Pembayaran Hutang berhasil dibuat!");
    }

    private function seedInitialCapital(Perusahaan $perusahaan)
    {
        // User requested to remove the 1 Billion capital.
        // We will rely on SimulasiDataSeeder for precision.
        return;
    }

    private function seedRandomExpense(Perusahaan $perusahaan, Carbon $date): void
    {
        $admin = User::where('perusahaan_id', $perusahaan->id)->first() ?? User::first();
        if (!$admin) return;

        $isIncome = rand(1, 10) > 7; // 30% income probability

        if ($isIncome) {
            $nominal = rand(5, 50) * 100000;
            DB::table('transaksi_operasional')->insert([
                'perusahaan_id' => $perusahaan->id,
                'tanggal' => $date,
                'operasional' => 'pemasukan',
                'kategori' => KategoriOperasional::TAMBAH_SALDO->value,
                'nominal' => $nominal,
                'keterangan' => 'Pemasukan Lain-lain',
                'pihak_id' => $admin->id,
                'pihak_type' => User::class,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $perusahaan->increment('saldo', $nominal);
        } else {
            $categories = [
                KategoriOperasional::UANG_JALAN->value,
                KategoriOperasional::BAHAN_BAKAR->value,
                KategoriOperasional::PERAWATAN->value,
                KategoriOperasional::LAIN_LAIN->value,
            ];
            $nominal = rand(1, 10) * 20000;
            DB::table('transaksi_operasional')->insert([
                'perusahaan_id' => $perusahaan->id,
                'tanggal' => $date,
                'operasional' => 'pengeluaran',
                'kategori' => $categories[array_rand($categories)],
                'nominal' => $nominal,
                'keterangan' => 'Biaya Operasional',
                'pihak_id' => $admin->id,
                'pihak_type' => User::class,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $perusahaan->decrement('saldo', $nominal);
        }
    }
}
