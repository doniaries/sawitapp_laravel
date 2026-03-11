<?php

namespace App\Filament\Widgets;

use App\Models\{Perusahaan, Penjual, TransaksiDo, LaporanKeuangan};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected static bool $shouldRegister = true;
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Calculate income using same logic as TransaksiDoStatWidget
        $incomingFunds = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->select([
                DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                DB::raw('COALESCE(SUM(CASE
                    WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                    THEN sisa_bayar
                    ELSE 0
                END), 0) as remaining_payments')
            ])->first();

        $operationalIncome = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pemasukan')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('nominal');

        // Calculate total monthly income
        $monthlyIncome = $incomingFunds->total_debt_payments +
            $incomingFunds->remaining_payments +
            $operationalIncome;

        // Calculate expenses
        $monthlyDOExpense = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('sub_total');

        $monthlyOperationalExpense = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pengeluaran')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('nominal');

        $monthlyExpense = $monthlyDOExpense + $monthlyOperationalExpense;

        // Get monthly transactions count
        $monthlyTransactions = TransaksiDo::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Calculate profit/loss
        $profit = $monthlyIncome - $monthlyExpense;
        $profitColor = $profit >= 0 ? 'success' : 'danger';
        $profitIcon = $profit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $profitPrefix = $profit >= 0 ? 'Rp ' : '-Rp ';
        $profitDescription = $profit >= 0 ? 'Keuntungan bulan ini' : 'Kerugian bulan ini';

        // Get current balance using same logic
        $totalIncomingFunds = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->select([
                DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                DB::raw('COALESCE(SUM(CASE
                    WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                    THEN sisa_bayar
                    ELSE 0
                END), 0) as remaining_payments')
            ])->first();

        $totalOperationalIncome = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pemasukan')
            ->sum('nominal');

        $totalIncome = $totalIncomingFunds->total_debt_payments +
            $totalIncomingFunds->remaining_payments +
            $totalOperationalIncome;

        $totalDOExpense = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->sum('sub_total');

        $totalOperationalExpense = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pengeluaran')
            ->sum('nominal');

        $totalExpense = $totalDOExpense + $totalOperationalExpense;
        $currentBalance = $totalIncome - $totalExpense;

        // Format date range
        $dateRange = "Periode: {$startOfMonth->format('d M Y')} - {$endOfMonth->format('d M Y')}";

        return [
            Stat::make('Sisa Saldo', 'Rp ' . number_format($currentBalance, 0, ',', '.'))
                ->description('Total saldo masuk - Total pengeluaran (Kumulatif)')
                ->icon('heroicon-m-banknotes')
                ->color($currentBalance >= 0 ? 'success' : 'danger'),

            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyIncome, 0, ',', '.'))
                ->description(sprintf(
                    "Hutang: Rp %s\nSisa: Rp %s\nOperasional: Rp %s",
                    number_format($incomingFunds->total_debt_payments, 0, ',', '.'),
                    number_format($incomingFunds->remaining_payments, 0, ',', '.'),
                    number_format($operationalIncome, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($monthlyExpense, 0, ',', '.'))
                ->description(sprintf(
                    "DO: Rp %s\nOperasional: Rp %s",
                    number_format($monthlyDOExpense, 0, ',', '.'),
                    number_format($monthlyOperationalExpense, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Total Transaksi', $monthlyTransactions)
                ->description($dateRange)
                ->icon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
