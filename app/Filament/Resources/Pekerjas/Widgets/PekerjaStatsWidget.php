<?php

namespace App\Filament\Resources\Pekerjas\Widgets;

use App\Models\Pekerja;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PekerjaStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pekerja', Pekerja::count())
                ->description('Total pekerja terdaftar')
                ->icon('heroicon-m-briefcase')
                ->color('info'),
        ];
    }
}
