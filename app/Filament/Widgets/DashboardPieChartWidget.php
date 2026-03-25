<?php

namespace App\Filament\Widgets;

use App\Models\JurnalKeuangan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DashboardPieChartWidget extends ChartWidget
{
    protected static bool $isLazy = true;
    protected ?string $heading = 'Distribusi Transaksi (Bulan Ini)';
    protected static ?int $sort = 10;

    protected function getData(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;
        $cacheKey = "dashboard_pie_chart_tenant_{$tenantId}_" . now()->format('YmdH');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () {
            $data = JurnalKeuangan::query()
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->select('jenis_transaksi', DB::raw('SUM(nominal) as total'))
            ->groupBy('jenis_transaksi')
            ->pluck('total', 'jenis_transaksi');

            return [
                'datasets' => [
                    [
                        'label' => 'Total Nominal',
                        'data' => $data->values()->toArray(),
                        'backgroundColor' => [
                            '#10b981', // green for Pemasukan
                            '#ef4444', // red for Pengeluaran
                        ],
                    ],
                ],
                'labels' => $data->keys()->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
