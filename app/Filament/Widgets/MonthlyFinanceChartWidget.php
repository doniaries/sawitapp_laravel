<?php

namespace App\Filament\Widgets;

use App\Models\{TransaksiDo, TransaksiOperasional, JurnalKeuangan};
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Facades\Filament;

class MonthlyFinanceChartWidget extends ChartWidget
{
    public static function shouldRegister(): bool
    {
        return \App\Providers\Filament\AdminPanelProvider::$dashboardWidgets['monthly_chart'] ?? true;
    }

    protected ?string $heading = 'Grafik Keuangan Bulanan';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $tenantId = Filament::getTenant()->id;

        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::now()->startOfYear()->addMonths($month - 1);
        });

        $monthlyData = $months->map(function ($date) use ($tenantId) {
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $stats = JurnalKeuangan::query()
                ->where('perusahaan_id', $tenantId)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->select([
                    'jenis_transaksi',
                    DB::raw('SUM(nominal) as total')
                ])
                ->groupBy('jenis_transaksi')
                ->get();

            $income = (float) $stats->where('jenis_transaksi', 'Pemasukan')->first()?->total ?? 0;
            $expense = (float) $stats->where('jenis_transaksi', 'Pengeluaran')->first()?->total ?? 0;
            $profit = $income - $expense;

            return [
                'date' => $date->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'profit' => $profit,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $monthlyData->pluck('income')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $monthlyData->pluck('expense')->toArray(),
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Keuntungan',
                    'data' => $monthlyData->pluck('profit')->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $monthlyData->pluck('date')->toArray(),
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
