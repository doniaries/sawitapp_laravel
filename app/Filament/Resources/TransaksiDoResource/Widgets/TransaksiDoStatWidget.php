<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan, Operasional};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '5s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // Get stats from cache or calculate
            return Cache::remember('transaksi-stats', 60, function () {
                // --- MONTHLY CALCULATIONS (For display in specific widgets) ---
                $incomingFundsMonthly = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->select([
                        DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                        DB::raw('COALESCE(SUM(CASE
                            WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                            THEN sisa_bayar
                            ELSE 0
                        END), 0) as remaining_payments')
                    ])->first();

                $operationalIncomeMonthly = DB::table('operasional')
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pemasukan')
                    ->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->sum('nominal');

                $totalIncomingMonthly = $incomingFundsMonthly->total_debt_payments +
                    $incomingFundsMonthly->remaining_payments +
                    $operationalIncomeMonthly;

                $totalDOMonthly = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->sum('sub_total');

                $totalOperationalMonthly = DB::table('operasional')
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pengeluaran')
                    ->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->sum('nominal');

                $totalExpenditureMonthly = $totalDOMonthly + $totalOperationalMonthly;

                // --- GLOBAL CALCULATIONS (Cumulative - For Sisa Saldo) ---
                $incomingFundsGlobal = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->select([
                        DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                        DB::raw('COALESCE(SUM(CASE
                            WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                            THEN sisa_bayar
                            ELSE 0
                        END), 0) as remaining_payments')
                    ])->first();

                $operationalIncomeGlobal = DB::table('operasional')
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pemasukan')
                    ->sum('nominal');

                $totalIncomingGlobal = $incomingFundsGlobal->total_debt_payments +
                    $incomingFundsGlobal->remaining_payments +
                    $operationalIncomeGlobal;

                $totalDOGlobal = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->sum('sub_total');

                $totalOperationalGlobal = DB::table('operasional')
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pengeluaran')
                    ->sum('nominal');

                $totalExpenditureGlobal = $totalDOGlobal + $totalOperationalGlobal;
                $remainingBalanceGlobal = $totalIncomingGlobal - $totalExpenditureGlobal;

                return [
                    // Remaining Balance (Global/Cumulative)
                    Stat::make('Sisa Saldo', 'Rp ' . number_format($remainingBalanceGlobal, 0, ',', '.'))
                        ->description('Total saldo (Kumulatif)')
                        ->descriptionIcon('heroicon-m-banknotes')
                        ->color($remainingBalanceGlobal >= 0 ? 'success' : 'danger'),

                    // Total Income (Monthly)
                    Stat::make('Uang Masuk (Bulan Ini)', 'Rp ' . number_format($totalIncomingMonthly, 0, ',', '.'))
                        ->description(sprintf(
                            "Bayar Hutang: Rp %s\nBayar Sisa: Rp %s\nOperasional: Rp %s",
                            number_format($incomingFundsMonthly->total_debt_payments, 0, ',', '.'),
                            number_format($incomingFundsMonthly->remaining_payments, 0, ',', '.'),
                            number_format($operationalIncomeMonthly, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success'),

                    // Total Expenditure (Monthly)
                    Stat::make('Pengeluaran (Bulan Ini)', 'Rp ' . number_format($totalExpenditureMonthly, 0, ',', '.'))
                        ->description(sprintf(
                            "DO: Rp %s\nOperasional: Rp %s",
                            number_format($totalDOMonthly, 0, ',', '.'),
                            number_format($totalOperationalMonthly, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger'),

                    Stat::make('Transaksi (Bulan Ini)', TransaksiDo::currentMonth()->count())
                        ->description(sprintf(
                            "tunai: %d | transfer: %d\ncair: %d | belum: %d",
                            TransaksiDo::currentMonth()->where('cara_bayar', 'tunai')->count(),
                            TransaksiDo::currentMonth()->where('cara_bayar', 'transfer')->count(),
                            TransaksiDo::currentMonth()->where('cara_bayar', 'cair di luar')->count(),
                            TransaksiDo::currentMonth()->where('cara_bayar', 'belum dibayar')->count()
                        ))
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('primary'),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error in TransaksiDoStatWidget:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan memuat data')
                    ->description($e->getMessage())
                    ->color('danger')
            ];
        }
    }

    // Refresh widget on various events
    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        Cache::forget('transaksi-stats');
    }
}
