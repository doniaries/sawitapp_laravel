<?php

namespace App\Filament\Resources\Pekerjas\Widgets;

use App\Models\Pekerja;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PekerjaStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;
        $cacheKey = "pekerja_stats_tenant_{$tenantId}_" . now()->format('YmdH');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            return [
            Stat::make('Total Pekerja', Pekerja::count())
                ->description('Total pekerja terdaftar')
                ->icon('heroicon-m-briefcase')
                ->color('info'),
            ];
        });
    }
}
