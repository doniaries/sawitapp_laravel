<?php

namespace App\Filament\Resources\PengajuanDanas\Widgets;

use App\Models\PengajuanDana;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PengajuanDanaStats extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '3s'; // Update setiap 3 detik

    protected function getStats(): array
    {
        $pendingCount = PengajuanDana::where('status', 'pending')->count();
        $pendingNominal = PengajuanDana::where('status', 'pending')->sum('nominal');
        $approvedMonth = PengajuanDana::where('status', 'disetujui')
            ->whereMonth('tanggal_proses', now()->month)
            ->whereYear('tanggal_proses', now()->year)
            ->sum('nominal');

        return [
            Stat::make('Pengajuan Pending', $pendingCount)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Total Dana Pending', 'Rp ' . number_format($pendingNominal, 0, ',', '.'))
                ->description('Total nominal yang diajukan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Total Cair Bulan Ini', 'Rp ' . number_format($approvedMonth, 0, ',', '.'))
                ->description('Dana yang disetujui bulan ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
