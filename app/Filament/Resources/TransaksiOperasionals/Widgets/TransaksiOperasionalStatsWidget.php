<?php

namespace App\Filament\Resources\TransaksiOperasionals\Widgets;

use App\Models\TransaksiOperasional;
use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransaksiOperasionalStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '10s';
    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;
        $today = now();

        // Today Stats
        $todayPemasukanQuery = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pemasukan')
            ->whereDate('tanggal', $today);
            
        $todayPemasukan = $todayPemasukanQuery->sum('nominal');
        $countPemasukan = $todayPemasukanQuery->count();

        $todayPengeluaranQuery = TransaksiOperasional::where('perusahaan_id', $tenantId)
            ->where('operasional', 'pengeluaran')
            ->whereDate('tanggal', $today);
            
        $todayPengeluaran = $todayPengeluaranQuery->sum('nominal');
        $countPengeluaran = $todayPengeluaranQuery->count();

        return [
            Stat::make('Pemasukan Hari Ini', 'Rp ' . number_format($todayPemasukan, 0, ',', '.'))
                ->description($countPemasukan . ' transaksi pemasukan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle')
                ->url(TransaksiOperasionalResource::getUrl('index', ['activeTab' => 'pemasukan'])),

            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($todayPengeluaran, 0, ',', '.'))
                ->description($countPengeluaran . ' transaksi pengeluaran hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-circle')
                ->url(TransaksiOperasionalResource::getUrl('index', ['activeTab' => 'pengeluaran'])),
        ];
    }
}
