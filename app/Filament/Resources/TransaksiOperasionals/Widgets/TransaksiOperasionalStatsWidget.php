<?php

namespace App\Filament\Resources\TransaksiOperasionals\Widgets;

use App\Models\TransaksiOperasional;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransaksiOperasionalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;
        $yesterday = now()->subDay();
        $bulanIni = now()->startOfMonth();

        // Yesterday Stats
        $yesterdayPemasukan = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pemasukan')
            ->whereDate('tanggal', $yesterday)
            ->sum('nominal');

        $yesterdayPengeluaran = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pengeluaran')
            ->whereDate('tanggal', $yesterday)
            ->sum('nominal');

        // Monthly Stats
        $totalPemasukan = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pemasukan')
            ->where('tanggal', '>=', $bulanIni)
            ->sum('nominal');

        $totalPengeluaran = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pengeluaran')
            ->where('tanggal', '>=', $bulanIni)
            ->sum('nominal');

        return [
            Stat::make('Operasional Kemarin (Masuk)', 'Rp ' . number_format($yesterdayPemasukan, 0, ',', '.'))
                ->description('Pemasukan operasional kemarin')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle'),

            Stat::make('Operasional Kemarin (Keluar)', 'Rp ' . number_format($yesterdayPengeluaran, 0, ',', '.'))
                ->description('Pengeluaran operasional kemarin')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-circle'),

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
