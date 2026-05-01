<?php

namespace App\Filament\Resources\TransaksiOperasionals\Widgets;

use App\Models\TransaksiOperasional;
use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransaksiOperasionalStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "transaksi_operasional_stats_tenant_{$tenantId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($tenantId) {
            $today = now()->toDateString();

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
                'pemasukan' => $todayPemasukan,
                'count_pemasukan' => $countPemasukan,
                'pengeluaran' => $todayPengeluaran,
                'count_pengeluaran' => $countPengeluaran,
            ];
        });

        return [
            Stat::make('Pemasukan Hari Ini', new \Illuminate\Support\HtmlString('<div class="text-base font-bold text-blue-600 dark:text-blue-400">' . money($stats['pemasukan'], 'IDR') . '</div>'))
                ->description($stats['count_pemasukan'] . ' transaksi pemasukan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle')
                ->url(TransaksiOperasionalResource::getUrl('index', ['activeTab' => 'pemasukan'])),

            Stat::make('Pengeluaran Hari Ini', new \Illuminate\Support\HtmlString('<div class="text-sm font-bold text-blue-600 dark:text-blue-400">' . money($stats['pengeluaran'], 'IDR') . '</div>'))
                ->description($stats['count_pengeluaran'] . ' transaksi pengeluaran hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-circle')
                ->url(TransaksiOperasionalResource::getUrl('index', ['activeTab' => 'pengeluaran'])),
        ];
    }
}
