<?php

namespace App\Filament\Resources\Supirs\Widgets;

use App\Models\Supir;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupirStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Supir', Supir::count())
                ->description('Total supir terdaftar')
                ->icon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Supir Berhutang', Supir::where('hutang', '>', 0)->count())
                ->description('Memiliki sisa kewajiban')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),
            Stat::make('Sudah Pernah Bayar', Supir::whereHas('riwayatPembayaran')->count())
                ->description('Pernah mencicil hutang')
                ->icon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
