<?php

namespace App\Filament\Resources\TransaksiDos\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '10s';
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            if (!$panel) return [];

            $tenant = \Filament\Facades\Filament::getTenant();
            if (!$tenant) return [];

            $today = today()->toDateString();
            $tenantId = $tenant->id;

            // Query transaksi DO hari ini
            $doStats = DB::table('transaksi_do')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $today)
                ->selectRaw('
                    COUNT(*) as count,
                    COALESCE(SUM(tonase), 0) as total_tonase,
                    COALESCE(SUM(sub_total), 0) as total_bruto,
                    COALESCE(SUM(pembayaran_hutang), 0) as total_potong_hutang,
                    COALESCE(SUM(upah_bongkar), 0) as total_bongkar,
                    COALESCE(SUM(biaya_lain), 0) as total_lain,
                    COALESCE(SUM(CASE WHEN cara_bayar = "tunai" THEN sisa_bayar ELSE 0 END), 0) as total_bayar_tunai,
                    COALESCE(SUM(CASE WHEN cara_bayar = "transfer" THEN sisa_bayar ELSE 0 END), 0) as total_bayar_transfer,
                    COALESCE(SUM(CASE WHEN cara_bayar = "tunai & transfer" THEN sisa_bayar ELSE 0 END), 0) as total_bayar_mixed,
                    COALESCE(SUM(CASE WHEN cara_bayar = "tunai" THEN 1 ELSE 0 END), 0) as tunai_count,
                    COALESCE(SUM(CASE WHEN cara_bayar = "transfer" THEN 1 ELSE 0 END), 0) as transfer_count,
                    COALESCE(SUM(CASE WHEN cara_bayar = "cair di luar" THEN 1 ELSE 0 END), 0) as cair_count,
                    COALESCE(SUM(CASE WHEN cara_bayar = "belum dibayar" THEN 1 ELSE 0 END), 0) as belum_count
                ')->first();

            // Query operasional hari ini
            $opStats = DB::table('transaksi_operasional')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $today)
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN operasional = "pemasukan" THEN nominal ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN operasional = "pengeluaran" THEN nominal ELSE 0 END), 0) as total_pengeluaran
                ')->first();

            // Query tambah saldo hari ini
            $saldoStats = DB::table('tambah_saldo')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $today)
                ->selectRaw('COALESCE(SUM(nominal), 0) as total_tambah_saldo')
                ->first();

            // Uang Masuk: Topup + Ops Pemasukan (Potong Hutang tidak masuk laci secara fisik)
            $totalIncoming = (float)$opStats->total_pemasukan
                + (float)$saldoStats->total_tambah_saldo;

            // Uang Keluar: Sisa Bayar Tunai + Bongkar + Lain + Ops Pengeluaran
            $totalExpenditure = (float)$doStats->total_bayar_tunai
                + (float)$doStats->total_bayar_mixed 
                + (float)$doStats->total_bongkar
                + (float)$doStats->total_lain
                + (float)$opStats->total_pengeluaran;

            $currentSaldo = \App\Models\Perusahaan::find($tenantId)->saldo ?? 0;

            return [
                Stat::make('Saldo Kas Perusahaan', money($currentSaldo, 'IDR'))
                        ->description('Total saldo tersedia saat ini')
                        ->descriptionIcon('heroicon-m-wallet')
                        ->color($currentSaldo < 0 ? 'danger' : 'success'),

                    Stat::make('DO Hari Ini', (int)$doStats->count)
                        ->description('Total Bruto: ' . money($doStats->total_bruto, 'IDR'))
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('info'),

                    Stat::make('Total Tonase', number_format($doStats->total_tonase, 0, ',', '.') . ' Kg')
                        ->description('Volume buah masuk hari ini')
                        ->descriptionIcon('heroicon-m-scale')
                        ->color('warning'),

                    Stat::make('Uang Masuk (Hari Ini)', new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-success-600 text-white font-bold text-base shadow-sm">
                            ' . money($totalIncoming, 'IDR') . '
                        </div>
                    '))
                        ->description(sprintf(
                            'Topup: %s | Ops: %s',
                            money($saldoStats->total_tambah_saldo, 'IDR'),
                            money($opStats->total_pemasukan, 'IDR')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success'),

                    Stat::make('Pengeluaran (Hari Ini)', new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-danger-600 text-white font-bold text-base shadow-sm">
                            ' . money($totalExpenditure, 'IDR') . '
                        </div>
                    '))
                        ->description(sprintf(
                            'Bayar Tunai: %s | Ops/Biaya: %s',
                            money($doStats->total_bayar_tunai + $doStats->total_bayar_mixed, 'IDR'),
                            money($opStats->total_pengeluaran + $doStats->total_bongkar + $doStats->total_lain, 'IDR')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger'),

                    Stat::make('Rekap Cara Bayar', (int)$doStats->count . ' DO')
                        ->description(sprintf(
                            'Tunai: %d | Transfer: %d | Cair: %d | Belum: %d',
                            $doStats->tunai_count,
                            $doStats->transfer_count,
                            $doStats->cair_count,
                            $doStats->belum_count
                        ))
                        ->descriptionIcon('heroicon-m-credit-card')
                        ->color('warning'),
                ];
        } catch (\Exception $e) {
            Log::error('TransaksiDoStatWidget Error:', ['message' => $e->getMessage()]);

            return [
                Stat::make('Error', 'Gagal memuat data')
                    ->description($e->getMessage())
                    ->color('danger')
            ];
        }
    }

    #[On(['transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'refresh-widget'])]
    public function refresh(): void
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        if ($tenantId) {
            Cache::forget("transaksi-do-stats-{$tenantId}-" . today()->toDateString());
        }
    }
}
