<?php

namespace App\Filament\Resources\Penjuals\Widgets;

use App\Models\Penjual;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '15s';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "penjual_stats_overview_tenant_{$tenantId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            // Hitung statistik secara efisien
            $totalPenjual = Penjual::query()->count();
            
            // Gunakan scope untuk mendapatkan sisa hutang yang akurat
            $allPenjual = Penjual::query()->withSisaHutang()->get();
            $totalSisaHutang = $allPenjual->sum('sisa_hutang_sum');
            $penjualDenganHutang = $allPenjual->where('sisa_hutang_sum', '>', 0)->count();
            
            $rataHutang = $penjualDenganHutang > 0 ? ($totalSisaHutang / $penjualDenganHutang) : 0;

            return [
                'totalPenjual' => $totalPenjual,
                'totalSisaHutang' => $totalSisaHutang,
                'penjualDenganHutang' => $penjualDenganHutang,
                'rataHutang' => $rataHutang,
            ];
        });

        $blueBadgeStyle = 'text-sm font-bold text-white bg-blue-600 dark:bg-blue-500 px-2 py-1 rounded shadow-sm inline-block';

        return [
            Stat::make('Total Penjual', number_format($stats['totalPenjual'], 0, ',', '.'))
                ->icon('heroicon-o-users'),

            Stat::make('Total Bayar ke Penjual', new \Illuminate\Support\HtmlString('<div class="' . $blueBadgeStyle . '">Rp ' . number_format($stats['totalSisaHutang'], 0, ',', '.') . '</div>'))
                ->description($stats['penjualDenganHutang'] . ' penjual dengan sisa bayar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Rata-rata Bayar', new \Illuminate\Support\HtmlString('<div class="text-sm font-bold text-blue-600 dark:text-blue-400">Rp ' . number_format($stats['rataHutang'], 0, ',', '.') . '</div>'))
                ->description('Per penjual aktif')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->icon('heroicon-o-calculator'),
        ];
    }
}
