<?php

namespace App\Filament\Resources\TransaksiDos\Widgets;

use App\Models\{TransaksiDo, Perusahaan, Operasional};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '10s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // Get stats from cache or calculate
            return Cache::remember('transaksi-stats', 60, function () {
                $tenantId = \Filament\Facades\Filament::getTenant()->id;
                $today = now();

                // --- TODAY CALCULATIONS ---
                $todayStats = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->whereDate('tanggal', $today)
                    ->select([
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(sub_total) as total')
                    ])->first();

                // --- TODAY CALCULATIONS (Tenant Scoped) ---
                $incomingFundsToday = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->whereDate('tanggal', $today)
                    ->select([
                        DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                        DB::raw('COALESCE(SUM(CASE
                            WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                            THEN sisa_bayar
                            ELSE 0
                        END), 0) as remaining_payments')
                    ])->first();

                $operationalIncomeToday = DB::table('transaksi_operasional')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pemasukan')
                    ->whereDate('tanggal', $today)
                    ->sum('nominal');

                $totalIncomingToday = $incomingFundsToday->total_debt_payments +
                    $incomingFundsToday->remaining_payments +
                    $operationalIncomeToday;

                $totalDOToday = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->whereDate('tanggal', $today)
                    ->sum('sub_total');

                $totalOperationalToday = DB::table('transaksi_operasional')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pengeluaran')
                    ->whereDate('tanggal', $today)
                    ->sum('nominal');

                $totalExpenditureToday = $totalDOToday + $totalOperationalToday;

                // --- GLOBAL CALCULATIONS (Cumulative - Scoped) ---
                $incomingFundsGlobal = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->select([
                        DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                        DB::raw('COALESCE(SUM(CASE
                            WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                            THEN sisa_bayar
                            ELSE 0
                        END), 0) as remaining_payments')
                    ])->first();

                $operationalIncomeGlobal = DB::table('transaksi_operasional')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pemasukan')
                    ->sum('nominal');

                $totalIncomingGlobal = $incomingFundsGlobal->total_debt_payments +
                    $incomingFundsGlobal->remaining_payments +
                    $operationalIncomeGlobal;

                $totalDOGlobal = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->sum('sub_total');

                $totalOperationalGlobal = DB::table('transaksi_operasional')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pengeluaran')
                    ->sum('nominal');

                $totalExpenditureGlobal = $totalDOGlobal + $totalOperationalGlobal;
                $remainingBalanceGlobal = $totalIncomingGlobal - $totalExpenditureGlobal;

                return [
                    Stat::make('DO Hari Ini', $todayStats->count ?? 0)
                        ->description('Total: Rp ' . number_format($todayStats->total ?? 0, 0, ',', '.'))
                        ->descriptionIcon('heroicon-m-clock')
                        ->color('info'),

                    // Remaining Balance (Global/Cumulative)
                    Stat::make('Sisa Saldo', 'Rp ' . number_format($remainingBalanceGlobal, 0, ',', '.'))
                        ->description('Total saldo (Kumulatif)')
                        ->descriptionIcon('heroicon-m-banknotes')
                        ->color($remainingBalanceGlobal >= 0 ? 'success' : 'danger'),

                    // Total Income (Today)
                    Stat::make('Uang Masuk (Hari Ini)', 'Rp ' . number_format($totalIncomingToday, 0, ',', '.'))
                        ->description(sprintf(
                            "Bayar Hutang: Rp %s\nBayar Sisa: Rp %s\nOperasional: Rp %s",
                            number_format($incomingFundsToday->total_debt_payments, 0, ',', '.'),
                            number_format($incomingFundsToday->remaining_payments, 0, ',', '.'),
                            number_format($operationalIncomeToday, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success'),

                    // Total Expenditure (Today)
                    Stat::make('Pengeluaran (Hari Ini)', 'Rp ' . number_format($totalExpenditureToday, 0, ',', '.'))
                        ->description(sprintf(
                            "DO: Rp %s\nOperasional: Rp %s",
                            number_format($totalDOToday, 0, ',', '.'),
                            number_format($totalOperationalToday, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger'),

                    Stat::make('Transaksi (Hari Ini)', TransaksiDo::where('perusahaan_id', $tenantId)->whereDate('tanggal', $today)->count())
                        ->description(sprintf(
                            "tunai: %d | transfer: %d\ncair: %d | belum: %d",
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereDate('tanggal', $today)->where('cara_bayar', 'tunai')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereDate('tanggal', $today)->where('cara_bayar', 'transfer')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereDate('tanggal', $today)->where('cara_bayar', 'cair di luar')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereDate('tanggal', $today)->where('cara_bayar', 'belum dibayar')->count()
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
