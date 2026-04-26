<?php

namespace App\Filament\Resources\Supirs\Widgets;

use App\Models\Supir;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupirStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "supir_stats_tenant_{$tenantId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            // Hitung statistik secara efisien di level database
            $totalSupir = \App\Models\Supir::query()->count();
            
            // Hitung total sisa hutang tanpa loading semua model (DRY & Performance)
            $totalHutangAwal = \App\Models\Supir::query()->sum('hutang');
            $totalPembayaran = \App\Models\PembayaranHutang::query()
                ->whereNotNull('supir_id')
                ->sum('nominal');
            
            $totalSisaHutang = $totalHutangAwal - $totalPembayaran;
            
            // Hitung jumlah supir yang masih memiliki hutang
            $supirDenganHutang = \App\Models\Supir::query()
                ->whereRaw('hutang > (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_hutang WHERE supir_id = supirs.id)')
                ->count();
            
            return [
                'totalSupir' => $totalSupir,
                'totalSisaHutang' => $totalSisaHutang,
                'supirDenganHutang' => $supirDenganHutang,
            ];
        });

        $blueBadgeStyle = 'text-base font-bold text-white bg-blue-600 dark:bg-blue-500 px-3 py-1 rounded-lg shadow-md inline-block transition-all hover:scale-105';

        return [
            Stat::make('Total Supir', number_format($stats['totalSupir'], 0, ',', '.'))
                ->icon('heroicon-m-user-group')
                ->description('Total supir terdaftar')
                ->color('info'),
            
            Stat::make('Total Sisa Bayar', new \Illuminate\Support\HtmlString('<div class="' . $blueBadgeStyle . '">Rp ' . number_format($stats['totalSisaHutang'], 0, ',', '.') . '</div>'))
                ->description($stats['supirDenganHutang'] . ' supir memiliki sisa kewajiban')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Supir Aktif', number_format($stats['totalSupir'], 0, ',', '.'))
                ->description('Driver yang tersedia')
                ->icon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
