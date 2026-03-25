<?php

namespace App\Filament\Resources\Penjuals\Widgets;

use App\Models\Penjual;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Penjual', Penjual::count())
                ->description('Total penjual terdaftar')
                ->icon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Penjual Berhutang', Penjual::where('hutang', '>', 0)->count())
                ->description('Memiliki sisa kewajiban')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),
            Stat::make('Sudah Pernah Bayar', Penjual::whereHas('riwayatPembayaran')->count())
                ->description('Pernah mencicil hutang')
                ->icon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
