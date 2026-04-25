<?php

namespace App\Filament\Resources\TambahSaldos\Widgets;

use App\Models\TambahSaldo as PengajuanDana;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TambahSaldoStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s'; // Update setiap 10 detik

    protected function getStats(): array
    {
        $todayNominal = PengajuanDana::whereDate('tanggal', today())->sum('nominal');
        $monthNominal = PengajuanDana::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');
        $totalNominal = PengajuanDana::sum('nominal');

        return [
            Stat::make('Total Topup Hari Ini', 'Rp ' . number_format($todayNominal, 0, ',', '.'))
                ->description('Saldo masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Topup Bulan Ini', 'Rp ' . number_format($monthNominal, 0, ',', '.'))
                ->description('Saldo masuk bulan ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Total Keseluruhan', 'Rp ' . number_format($totalNominal, 0, ',', '.'))
                ->description('Total semua saldo masuk')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),
        ];
    }
}
