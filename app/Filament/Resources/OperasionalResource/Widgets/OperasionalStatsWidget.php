<?php

namespace App\Filament\Resources\OperasionalResource\Widgets;

use App\Models\Operasional;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperasionalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Hitung total pemasukan dan pengeluaran bulan ini
        $bulanIni = now()->startOfMonth();
        
        $totalPemasukan = Operasional::where('operasional', 'pemasukan')
            ->where('tanggal', '>=', $bulanIni)
            ->sum('nominal');

        $totalPengeluaran = Operasional::where('operasional', 'pengeluaran')
            ->where('tanggal', '>=', $bulanIni)
            ->sum('nominal');

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPemasukan, 0, ',', '.'))
                ->description('Bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description('Bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-circle'),
        ];
    }
}
