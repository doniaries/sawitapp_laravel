<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiOperasional;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\Pekerja;
use App\Models\Perusahaan;
use App\Models\User;
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

            // 1. Generate Daily Expenses for last 6 months
            $startDate = now()->subMonths(6)->startOfMonth();
            $currentDate = clone $startDate;

            while ($currentDate < now()->startOfDay()) {
                $daysInMonth = $currentDate->daysInMonth;
                
                // Expense frequency: 5-10 times per month
                $expenseCount = rand(5, 10);
                for ($i = 0; $i < $expenseCount; $i++) {
                    $tanggal = (clone $currentDate)->setDay(rand(1, $daysInMonth))->setHour(rand(8, 17));
                    if ($tanggal >= now()) continue;

                    $this->seedRandomExpense($tanggal, $perusahaanId);
                }

                // Standalone Debt Payments: 2-5 times per month
                $this->seedMonthlyPayments($currentDate, $perusahaanId, $daysInMonth);

                $currentDate->addMonth();
            }

            // Yesterday & Today
            for ($i = 0; $i < rand(1, 3); $i++) {
                $tanggal = now()->subDay()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $this->seedRandomExpense($tanggal, $perusahaanId);
            }

            for ($i = 0; $i < rand(1, 3); $i++) {
                $maxMinutes = max(1, now()->diffInMinutes(now()->startOfDay()));
                $tanggal = now()->startOfDay()->addMinutes(rand(0, $maxMinutes));
                $this->seedRandomExpense($tanggal, $perusahaanId);
            }
        }

        $this->command->info("Simulasi Operasional & Pembayaran Hutang berhasil dibuat!");
    }

    private function seedRandomExpense($tanggal, $perusahaanId)
    {
        $categories = [
            KategoriOperasional::BAHAN_BAKAR,
            KategoriOperasional::PERAWATAN,
            KategoriOperasional::LAIN_LAIN,
            KategoriOperasional::UANG_JALAN,
        ];
        $kat = $categories[array_rand($categories)];

        TransaksiOperasional::create([
            'tanggal' => $tanggal,
            'operasional' => 'pengeluaran',
            'kategori' => $kat,
            'nominal' => rand(50000, 500000),
            'keterangan' => "Biaya {$kat->label()} (Simulasi)",
            'perusahaan_id' => $perusahaanId,
            'pihak_type' => User::class,
            'pihak_id' => 1, // Admin
        ]);
    }

    private function seedMonthlyPayments($monthDate, $perusahaanId, $daysInMonth)
    {
        $entities = [
            ['model' => Penjual::class, 'slug' => 'penjual'],
            ['model' => Supir::class, 'slug' => 'supir'],
            ['model' => Pekerja::class, 'slug' => 'pekerja'],
        ];

        foreach ($entities as $entity) {
            $records = ($entity['model'])::where('perusahaan_id', $perusahaanId)
                ->where('hutang', '>', 100000)
                ->get();

            if ($records->isEmpty()) continue;

            $toPay = $records->random(min($records->count(), rand(1, 2)));
            foreach ($toPay as $record) {
                $nominal = rand(50000, 200000);
                $tanggal = (clone $monthDate)->setDay(rand(1, $daysInMonth))->format('Y-m-d H:i:s');
                
                $this->processDebtPayment->execute(
                    $record,
                    (float) $nominal,
                    $tanggal,
                    'tunai',
                    'Bayar Hutang (Simulasi)'
                );
            }
        }
    }
}
