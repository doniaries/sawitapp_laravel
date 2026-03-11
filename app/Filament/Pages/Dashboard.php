<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use App\Filament\Widgets\{DashboardStatsWidget, DailyFinanceChartWidget, MonthlyFinanceChartWidget, TopHutangPenjualWidget, TopPenjualTonaseWidget, TransaksiTerakhir};
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    /**
     * Mendaftarkan widget yang akan ditampilkan di dashboard
     */
    public function getWidgets(): array
    {
        return [
            // AccountWidget::class,
            DashboardStatsWidget::class,
            TopHutangPenjualWidget::class,
            TopPenjualTonaseWidget::class,
            DailyFinanceChartWidget::class,
            MonthlyFinanceChartWidget::class,
        ];
    }

    /**
     * Mengatur jumlah kolom untuk layout widget
     * xs: Extra small screens (default: 1)
     * sm: Small screens and up (default: 1)
     * md: Medium screens and up (default: 2)
     * lg: Large screens and up (default: 3)
     * xl: Extra large screens and up (default: 3)
     * 2xl: 2X Extra large screens and up (default: 3)
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'xl' => 4,
        ];
    }

    /**
     * Mengatur polling interval untuk auto-refresh dashboard
     */
    public function getPollingInterval(): ?string
    {
        return '15s';
    }
}
