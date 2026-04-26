<?php

namespace App\Filament\Resources\TransaksiDos\Widgets;

use App\Models\{TransaksiDo};
use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';
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

                return [
                    Stat::make('DO Hari Ini', ($todayStats->count ?? 0))
                        ->description('Total: Rp ' . number_format($todayStats->total ?? 0, 0, ',', '.'))
                        ->descriptionIcon('heroicon-m-clock')
                        ->color('info')
                        ->url(TransaksiDoResource::getUrl('index', ['activeTab' => 'hari_ini'])),

                    // Total Income (Today)
                    Stat::make('Uang Masuk (Hari Ini)', new \Illuminate\Support\HtmlString('<div class="text-base font-bold text-blue-600 dark:text-blue-400">Rp ' . number_format($totalIncomingToday, 0, ',', '.') . '</div>'))
                        ->description(sprintf(
                            "Bayar Hutang: Rp %s | Bayar Sisa: Rp %s\nOperasional: Rp %s",
                            number_format($incomingFundsToday->total_debt_payments, 0, ',', '.'),
                            number_format($incomingFundsToday->remaining_payments, 0, ',', '.'),
                            number_format($operationalIncomeToday, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success')
                        ->url(JurnalKeuanganResource::getUrl('index', [
                            'activeTab' => 'hari_ini',
                            'tableFilters' => ['jenis_transaksi' => ['value' => 'Pemasukan']]
                        ])),
 
                    // Total Expenditure (Today)
                    Stat::make('Pengeluaran (Hari Ini)', new \Illuminate\Support\HtmlString('<div class="text-sm font-bold text-blue-600 dark:text-blue-400">Rp ' . number_format($totalExpenditureToday, 0, ',', '.') . '</div>'))
                        ->description(sprintf(
                            "DO: Rp %s | Operasional: Rp %s",
                            number_format($totalDOToday, 0, ',', '.'),
                            number_format($totalOperationalToday, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger')
                        ->url(JurnalKeuanganResource::getUrl('index', [
                            'activeTab' => 'hari_ini',
                            'tableFilters' => ['jenis_transaksi' => ['value' => 'Pengeluaran']]
                        ])),

                    Stat::make('Transaksi (Hari Ini)', TransaksiDo::where('perusahaan_id', $tenantId)->whereNull('deleted_at')->whereDate('tanggal', $today)->count())
                        ->description(sprintf(
                            "tunai: %d | transfer: %d | cair: %d | belum: %d",
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereNull('deleted_at')->whereDate('tanggal', $today)->where('cara_bayar', 'tunai')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereNull('deleted_at')->whereDate('tanggal', $today)->where('cara_bayar', 'transfer')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereNull('deleted_at')->whereDate('tanggal', $today)->where('cara_bayar', 'cair di luar')->count(),
                            TransaksiDo::where('perusahaan_id', $tenantId)->whereNull('deleted_at')->whereDate('tanggal', $today)->where('cara_bayar', 'belum dibayar')->count()
                        ))
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('warning')
                        ->url(TransaksiDoResource::getUrl('index', ['activeTab' => 'hari_ini'])),
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
