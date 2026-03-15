<?php

namespace App\Filament\Widgets;

use App\Models\{TransaksiDo, TransaksiOperasional, JurnalKeuangan};
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Facades\Filament;

class DailyFinanceChartWidget extends ChartWidget
{
    public static function shouldRegister(): bool
    {
        return \App\Providers\Filament\AdminPanelProvider::$dashboardWidgets['daily_chart'] ?? true;
    }

    protected ?string $heading = 'Grafik Keuangan Harian';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $tenantId = Filament::getTenant()->id;
        
        $days = collect(range(1, Carbon::now()->daysInMonth))->map(function ($day) {
            return Carbon::now()->startOfMonth()->addDays($day - 1);
        });

        $dailyData = $days->map(function ($date) use ($tenantId) {
            $stats = JurnalKeuangan::query()
                ->where('perusahaan_id', $tenantId)
                ->whereDate('tanggal', $date)
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
                'date' => $date->format('d M'),
                'income' => $income,
                'expense' => $expense,
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
