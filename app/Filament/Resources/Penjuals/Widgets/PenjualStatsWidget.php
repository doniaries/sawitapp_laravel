<?php

namespace App\Filament\Resources\Penjuals\Widgets;

use App\Models\Penjual;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;
        $cacheKey = "penjual_stats_tenant_{$tenantId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            $totalHutangAwal = \App\Models\Penjual::query()->sum('hutang');
            $totalPembayaran = \App\Models\PembayaranHutang::query()
                ->whereNotNull('penjual_id')
                ->sum('nominal');

            $totalSisaHutang = $totalHutangAwal - $totalPembayaran;

            return [
                'total_penjual' => \App\Models\Penjual::count(),
                'penjual_berhutang' => \App\Models\Penjual::query()
                    ->whereRaw('hutang > (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_hutang WHERE penjual_id = penjuals.id)')
                    ->count(),
                'total_sisa_hutang' => $totalSisaHutang,
            ];
        });

        $blueBadgeStyle = 'text-base font-bold text-white bg-blue-600 dark:bg-blue-500 px-3 py-1 rounded-lg shadow-md inline-block transition-all hover:scale-105';

        return [
            Stat::make('Total Penjual', number_format($stats['total_penjual'], 0, ',', '.'))
                ->description('Total penjual terdaftar')
                ->icon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Total Sisa Bayar', new \Illuminate\Support\HtmlString('<div class="' . $blueBadgeStyle . '">Rp ' . number_format($stats['total_sisa_hutang'], 0, ',', '.') . '</div>'))
                ->description($stats['penjual_berhutang'] . ' penjual memiliki sisa kewajiban')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Penjual Aktif', number_format($stats['total_penjual'], 0, ',', '.'))
                ->description('Sirkulasi transaksi aktif')
                ->icon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
