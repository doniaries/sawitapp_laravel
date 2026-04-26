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
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            $tenant = \Filament\Facades\Filament::getTenant();
            if (!$tenant) return [];

            $cacheKey = "transaksi-stats-tenant-{$tenant->id}";

            // Get stats from cache or calculate
            return Cache::remember($cacheKey, 60, function () use ($tenant) {
                $tenantId = $tenant->id;
                $today = now()->toDateString();

                // 1. Combine DO stats into one query
                $doStats = DB::table('transaksi_do')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->whereDate('tanggal', $today)
                    ->selectRaw('
                        COUNT(*) as count,
                        SUM(sub_total) as total,
                        SUM(pembayaran_hutang) as total_debt_payments,
                        SUM(CASE WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar") THEN sisa_bayar ELSE 0 END) as remaining_payments,
                        SUM(CASE WHEN cara_bayar = "tunai" THEN 1 ELSE 0 END) as tunai_count,
                        SUM(CASE WHEN cara_bayar = "transfer" THEN 1 ELSE 0 END) as transfer_count,
                        SUM(CASE WHEN cara_bayar = "cair di luar" THEN 1 ELSE 0 END) as cair_count,
                        SUM(CASE WHEN cara_bayar = "belum dibayar" THEN 1 ELSE 0 END) as belum_count
                    ')->first();

                // 2. Combine Operational stats into one query
                $opStats = DB::table('transaksi_operasional')
                    ->where('perusahaan_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->whereDate('tanggal', $today)
                    ->selectRaw('
                        SUM(CASE WHEN operasional = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN operasional = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran
                    ')->first();

                $totalIncomingToday = (float)$doStats->total_debt_payments +
                    (float)$doStats->remaining_payments +
                    (float)$opStats->total_pemasukan;

                $totalExpenditureToday = (float)$doStats->total + (float)$opStats->total_pengeluaran;

                return [
                    Stat::make('DO Hari Ini', (int)$doStats->count)
                        ->description('Total: Rp ' . number_format($doStats->total ?? 0, 0, ',', '.'))
                        ->descriptionIcon('heroicon-m-clock')
                        ->color('info')
                        ->url(TransaksiDoResource::getUrl('index', ['activeTab' => 'hari_ini'])),

                    // Total Income (Today)
                    Stat::make('Uang Masuk (Hari Ini)', new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-success-600 text-white font-bold text-base shadow-sm">
                            Rp ' . number_format($totalIncomingToday, 0, ',', '.') . '
                        </div>
                    '))
                        ->description(sprintf(
                            "Bayar Hutang: Rp %s | Bayar Sisa: Rp %s\nOperasional: Rp %s",
                            number_format($doStats->total_debt_payments ?? 0, 0, ',', '.'),
                            number_format($doStats->remaining_payments ?? 0, 0, ',', '.'),
                            number_format($opStats->total_pemasukan ?? 0, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success')
                        ->url(JurnalKeuanganResource::getUrl('index', [
                            'activeTab' => 'hari_ini',
                            'tableFilters' => ['jenis_transaksi' => ['value' => 'Pemasukan']]
                        ])),

                    // Total Expenditure (Today)
                    Stat::make('Pengeluaran (Hari Ini)', new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-danger-600 text-white font-bold text-base shadow-sm">
                            Rp ' . number_format($totalExpenditureToday, 0, ',', '.') . '
                        </div>
                    '))
                        ->description(sprintf(
                            "DO: Rp %s | Operasional: Rp %s",
                            number_format($doStats->total ?? 0, 0, ',', '.'),
                            number_format($opStats->total_pengeluaran ?? 0, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger')
                        ->url(JurnalKeuanganResource::getUrl('index', [
                            'activeTab' => 'hari_ini',
                            'tableFilters' => ['jenis_transaksi' => ['value' => 'Pengeluaran']]
                        ])),

                    Stat::make('Transaksi (Hari Ini)', (int)$doStats->count)
                        ->description(sprintf(
                            "tunai: %d | transfer: %d | cair: %d | belum: %d",
                            $doStats->tunai_count ?? 0,
                            $doStats->transfer_count ?? 0,
                            $doStats->cair_count ?? 0,
                            $doStats->belum_count ?? 0
                        ))
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('warning')
                        ->url(TransaksiDoResource::getUrl('index', ['activeTab' => 'hari_ini'])),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error in TransaksiDoStatWidget:', ['message' => $e->getMessage()]);

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
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        if ($tenantId) {
            Cache::forget("transaksi-stats-tenant-{$tenantId}");
        }
    }

}
