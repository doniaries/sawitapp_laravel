<?php

namespace App\Filament\Widgets;

use App\Models\{TransaksiDo, Operasional};
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyFinanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Keuangan Harian';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(1, Carbon::now()->daysInMonth))->map(function ($day) {
            return Carbon::now()->startOfMonth()->addDays($day - 1);
        });

        $dailyData = $days->map(function ($date) {
            // Get DO income
            $doIncome = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->select([
                    DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as debt_payments'),
                    DB::raw('COALESCE(SUM(CASE
                        WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                        THEN sisa_bayar
                        ELSE 0
                    END), 0) as remaining_payments')
                ])->first();

            // Get operational income
            $operationalIncome = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->where('operasional', 'pemasukan')
                ->sum('nominal');

            // Get expenses
            $doExpense = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->sum('sub_total');

            $operationalExpense = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->where('operasional', 'pengeluaran')
                ->sum('nominal');

            $totalIncome = $doIncome->debt_payments + $doIncome->remaining_payments + $operationalIncome;
            $totalExpense = $doExpense + $operationalExpense;
            $profit = $totalIncome - $totalExpense;

            return [
                'date' => $date->format('d M'),
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'profit' => $profit,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $dailyData->pluck('income')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $dailyData->pluck('expense')->toArray(),
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Keuntungan',
                    'data' => $dailyData->pluck('profit')->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $dailyData->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'fill' => true,
                ],
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
