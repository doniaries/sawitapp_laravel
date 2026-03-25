<?php

namespace App\Filament\Widgets;

use App\Models\JurnalKeuangan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DashboardPieChartWidget extends ChartWidget
{
    protected ?string $heading = 'Distribusi Transaksi (Bulan Ini)';
    protected static ?int $sort = 10;

    protected function getData(): array
    {
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
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
