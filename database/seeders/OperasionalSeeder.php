<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{TransaksiOperasional, Perusahaan, JurnalKeuangan, Penjual, Supir, Pekerja, User};
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

            // 1. Generate Daily Transactions for last 6 months
            $startDate = now()->subMonths(6)->startOfMonth();
            $currentDate = clone $startDate;

            while ($currentDate < now()->startOfDay()) {
                $daysInMonth = $currentDate->daysInMonth;
                
                // Frequency: 10-15 times per month
                $expenseCount = rand(10, 15);
                for ($i = 0; $i < $expenseCount; $i++) {
                    $tanggal = (clone $currentDate)->setDay(rand(1, $daysInMonth))->setHour(rand(8, 17));
                    if ($tanggal >= now()) continue;

                    $this->seedRandomExpense($perusahaan, $tanggal);
                }

                $currentDate->addMonth();
            }

            // Yesterday & Today
            for ($i = 0; $i < 5; $i++) {
                $tanggal = now()->subDay()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $this->seedRandomExpense($perusahaan, $tanggal);
            }

            for ($i = 0; $i < 10; $i++) { // More transactions for today
                $maxMinutes = max(1, now()->diffInMinutes(now()->startOfDay()));
                $tanggal = now()->startOfDay()->addMinutes(rand(0, $maxMinutes));
                $this->seedRandomExpense($perusahaan, $tanggal);
            }
        }

        $this->command->info("Simulasi Operasional & Pembayaran Hutang berhasil dibuat!");
    }

    private function seedRandomExpense(Perusahaan $perusahaan, Carbon $date): void
    {
        $isToday = $date->isToday();
        $admin = User::where('perusahaan_id', $perusahaan->id)->first() ?? User::first();
        
        if (!$admin) return;

        // Tambahkan Modal Awal jika belum ada
        if ($date->format('Y-m-d') === Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d')) {
             TransaksiOperasional::create([
                'perusahaan_id' => $perusahaan->id,
                'tanggal' => $date,
                'operasional' => 'pemasukan',
                'kategori' => KategoriOperasional::TAMBAH_SALDO->value,
                'nominal' => 1000000000,
                'keterangan' => 'Suntikan Modal Awal Perusahaan',
                'tipe_nama' => 'user',
                'user_id' => $admin->id,
            ]);
        }

        $incomeChance = $isToday ? 0.7 : 0.2; 
        
        if (rand(1, 100) <= ($incomeChance * 100)) {
            TransaksiOperasional::create([
                'perusahaan_id' => $perusahaan->id,
                'tanggal' => $date,
                'operasional' => 'pemasukan',
                'kategori' => KategoriOperasional::TAMBAH_SALDO->value,
                'nominal' => rand(50, 200) * 1000000,
                'keterangan' => 'Hasil Penjualan CPO/Inti Sawit',
                'tipe_nama' => 'user',
                'user_id' => $admin->id,
            ]);
            
            if ($isToday) {
                TransaksiOperasional::create([
                    'perusahaan_id' => $perusahaan->id,
                    'tanggal' => $date,
                    'operasional' => 'pemasukan',
                    'kategori' => KategoriOperasional::TAMBAH_SALDO->value,
                    'nominal' => rand(10, 50) * 1000000,
                    'keterangan' => 'Bonus Pencapaian Target Pabrik',
                    'tipe_nama' => 'user',
                    'user_id' => $admin->id,
                ]);
            }
        }

        $expenseCount = rand(1, 2);
        for ($i = 0; $i < $expenseCount; $i++) {
            $nominal = rand(5, 50) * 100000;

            TransaksiOperasional::create([
                'perusahaan_id' => $perusahaan->id,
                'tanggal' => $date,
                'operasional' => 'pengeluaran',
                'kategori' => KategoriOperasional::LAIN_LAIN->value,
                'nominal' => $nominal,
                'keterangan' => 'Biaya ATK dan Listrik Kantor',
                'tipe_nama' => 'user',
                'user_id' => $admin->id,
            ]);
        }
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
