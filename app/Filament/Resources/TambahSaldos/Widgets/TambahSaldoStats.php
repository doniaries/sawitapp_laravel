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
        $pendingCount = PengajuanDana::where('status', 'pending')->count();
        $pendingNominal = PengajuanDana::where('status', 'pending')->sum('nominal');
        $approvedMonth = PengajuanDana::where('status', 'disetujui')
            ->whereMonth('tanggal_proses', now()->month)
            ->whereYear('tanggal_proses', now()->year)
            ->sum('nominal');

        return [
            Stat::make('Topup Pending', $pendingCount)
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Total Nominal Pending', 'Rp ' . number_format($pendingNominal, 0, ',', '.'))
                ->description('Total top up pending')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('warning'),
            Stat::make('Total Topup Bulan Ini', 'Rp ' . number_format($approvedMonth, 0, ',', '.'))
                ->description('Saldo masuk bulan ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
