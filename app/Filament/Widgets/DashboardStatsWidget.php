<?php

namespace App\Filament\Widgets;

use App\Models\{Perusahaan, Penjual, TransaksiDo, JurnalKeuangan};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

class DashboardStatsWidget extends BaseWidget
{
    public static function shouldRegister(): bool
    {
        return \App\Providers\Filament\AdminPanelProvider::$dashboardWidgets['stats'] ?? true;
    }

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant->id;
        
        $today = now();

        // 1. Current Balance (Tenant Scoped)
        $currentBalance = (float) $tenant->saldo;

        // 2. Daily Income Breakdown
        $dailyIncomeQuery = JurnalKeuangan::query()
            ->where('perusahaan_id', $tenantId)
            ->whereDate('tanggal', $today)
            ->where('jenis_transaksi', 'Pemasukan');

        $totalIncomeDaily = (float) $dailyIncomeQuery->sum('nominal');
        
        $incomeStats = $dailyIncomeQuery->select([
            'kategori',
            'sub_kategori',
            DB::raw('SUM(nominal) as total')
        ])->groupBy('kategori', 'sub_kategori')->get();

        $hutangIncome = $incomeStats->where('sub_kategori', 'Bayar Hutang')->sum('total');
        $sisaIncome = $incomeStats->where('sub_kategori', 'Pembayaran DO')->sum('total');
        $operasionalIncome = $incomeStats->where('kategori', 'Operasional')->sum('total');

        // 3. Daily Expense Breakdown
        $dailyExpenseQuery = JurnalKeuangan::query()
            ->where('perusahaan_id', $tenantId)
            ->whereDate('tanggal', $today)
            ->where('jenis_transaksi', 'Pengeluaran');

        $totalExpenseDaily = (float) $dailyExpenseQuery->sum('nominal');

        $expenseStats = $dailyExpenseQuery->select([
            'kategori',
            DB::raw('SUM(nominal) as total')
        ])->groupBy('kategori')->get();

        $doExpense = $expenseStats->where('kategori', 'DO')->sum('total');
        $operasionalExpense = $expenseStats->where('kategori', 'Operasional')->sum('total');

        // 4. Transaction Count
        $dailyTransactions = JurnalKeuangan::where('perusahaan_id', $tenantId)
            ->whereDate('tanggal', $today)
            ->count();

        // Format date range for display
        $dateRange = "Hari ini: {$today->format('d M Y')}";

        return [
            Stat::make('Sisa Saldo', 'Rp ' . number_format($currentBalance, 0, ',', '.'))
                ->description('Total saldo masuk - Total pengeluaran (Kumulatif)')
                ->icon('heroicon-m-banknotes')
                ->color($currentBalance >= 0 ? 'success' : 'danger'),

            Stat::make('Pemasukan Hari Ini', 'Rp ' . number_format($totalIncomeDaily, 0, ',', '.'))
                ->description(sprintf(
                    "Hutang: Rp %s Sisa: Rp %s\nOperasional: Rp %s",
                    number_format($hutangIncome, 0, ',', '.'),
                    number_format($sisaIncome, 0, ',', '.'),
                    number_format($operasionalIncome, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($totalExpenseDaily, 0, ',', '.'))
                ->description(sprintf(
                    "DO: Rp %s Operasional: Rp %s",
                    number_format($doExpense, 0, ',', '.'),
                    number_format($operasionalExpense, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Total Transaksi', $dailyTransactions)
                ->description($dateRange)
                ->icon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
