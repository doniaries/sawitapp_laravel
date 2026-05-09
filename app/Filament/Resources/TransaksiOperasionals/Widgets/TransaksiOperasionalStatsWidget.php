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
        $cacheKey = "transaksi_operasional_stats_tenant_{$tenantId}_" . now()->format('Y-m-d-H');

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($tenantId) {
            $startOfToday = today()->startOfDay()->toDateTimeString();
            $endOfToday = today()->endOfDay()->toDateTimeString();

            // Pemasukan Today
            $todayPemasukan = \Illuminate\Support\Facades\DB::table('transaksi_operasional')
                ->where('perusahaan_id', $tenantId)
                ->where('operasional', 'pemasukan')
                ->whereNull('deleted_at')
                ->where('tanggal', '>=', $startOfToday)
                ->where('tanggal', '<=', $endOfToday)
                ->selectRaw('COUNT(*) as count, SUM(nominal) as total')
                ->first();

            // Pengeluaran Today
            $todayPengeluaran = \Illuminate\Support\Facades\DB::table('transaksi_operasional')
                ->where('perusahaan_id', $tenantId)
                ->where('operasional', 'pengeluaran')
                ->whereNull('deleted_at')
                ->where('tanggal', '>=', $startOfToday)
                ->where('tanggal', '<=', $endOfToday)
                ->selectRaw('COUNT(*) as count, SUM(nominal) as total')
                ->first();

            return [
                'pemasukan' => $todayPemasukan->total ?? 0,
                'count_pemasukan' => $todayPemasukan->count ?? 0,
                'pengeluaran' => $todayPengeluaran->total ?? 0,
                'count_pengeluaran' => $todayPengeluaran->count ?? 0,
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
