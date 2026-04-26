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
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "pekerja_stats_tenant_{$tenantId}";

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            // Hitung statistik secara efisien di level database
            $totalPekerja = \App\Models\Pekerja::query()->count();
            
            // Hitung total sisa hutang tanpa loading semua model (DRY & Performance)
            $totalHutangAwal = \App\Models\Pekerja::query()->sum('hutang');
            $totalPembayaran = \App\Models\PembayaranHutang::query()
                ->whereNotNull('pekerja_id')
                ->sum('nominal');
            
            $totalSisaHutang = $totalHutangAwal - $totalPembayaran;
            
            // Hitung jumlah pekerja yang masih memiliki hutang
            $pekerjaDenganHutang = \App\Models\Pekerja::query()
                ->whereRaw('hutang > (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_hutang WHERE pekerja_id = pekerja.id)')
                ->count();
            
            return [
                'totalPekerja' => $totalPekerja,
                'totalSisaHutang' => $totalSisaHutang,
                'pekerjaDenganHutang' => $pekerjaDenganHutang,
            ];
        });

        $blueBadgeStyle = 'text-base font-bold text-white bg-blue-600 dark:bg-blue-500 px-3 py-1 rounded-lg shadow-md inline-block transition-all hover:scale-105';

        return [
            Stat::make('Total Pekerja', number_format($stats['totalPekerja'], 0, ',', '.'))
                ->icon('heroicon-m-briefcase')
                ->description('Total pekerja terdaftar')
                ->color('info'),

            Stat::make('Total Sisa Bayar', new \Illuminate\Support\HtmlString('<div class="' . $blueBadgeStyle . '">Rp ' . number_format($stats['totalSisaHutang'], 0, ',', '.') . '</div>'))
                ->description($stats['pekerjaDenganHutang'] . ' pekerja memiliki sisa kewajiban')
                ->icon('heroicon-m-banknotes')
                ->color('danger'),
        ];
    }
}
