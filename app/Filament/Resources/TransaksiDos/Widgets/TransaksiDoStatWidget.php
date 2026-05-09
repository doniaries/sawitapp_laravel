<?php

namespace App\Filament\Resources\TransaksiDos\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = null; // Matikan polling agar tidak membebani server terus-menerus
    protected static bool $isLazy = true; // Aktifkan Lazy Loading agar tabel muncul duluan
    protected int | string | array $columnSpan = 'full';

    public ?string $startDate = null;
    public ?string $endDate = null;

    #[On('filter-transaksi')]
    public function updateFilter(array $data): void
    {
        $this->startDate = $data['startDate'] ?? null;
        $this->endDate = $data['endDate'] ?? null;
    }

    protected function getStats(): array
    {
        try {
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            if (!$panel) return [];

            $tenant = \Filament\Facades\Filament::getTenant();
            if (!$tenant) return [];

            $startDate = $this->startDate ?: today()->toDateString();
            $endDate = $this->endDate ?: today()->toDateString();
            $tenantId = $tenant->id;

            // Jika startDate dan endDate null (kasus tab "Semua"), kita tampilkan semua data
            $isFiltering = !empty($this->startDate) || !empty($this->endDate);

            // Query transaksi DO
            $doStats = DB::table('transaksi_do')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->when($isFiltering, function ($q) use ($startDate, $endDate) {
                    return $q->whereDate('tanggal', '>=', $startDate)
                        ->whereDate('tanggal', '<=', $endDate);
                }, fn($q) => $q->whereDate('tanggal', today()))
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

            // Query operasional dengan breakdown
            $opStats = DB::table('transaksi_operasional')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->when($isFiltering, function ($q) use ($startDate, $endDate) {
                    return $q->whereDate('tanggal', '>=', $startDate)
                        ->whereDate('tanggal', '<=', $endDate);
                }, fn($q) => $q->whereDate('tanggal', today()))
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN operasional = "pemasukan" THEN nominal ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN operasional = "pengeluaran" THEN nominal ELSE 0 END), 0) as total_pengeluaran,
                    COALESCE(SUM(CASE WHEN operasional = "pemasukan" AND pihak_type = "Supir" THEN nominal ELSE 0 END), 0) as htg_supir,
                    COALESCE(SUM(CASE WHEN operasional = "pengeluaran" AND (keterangan LIKE "%Belanja Kasir%" OR keterangan LIKE "%belanja kasir%") THEN nominal ELSE 0 END), 0) as belanja_kasir
                ')->first();

            // Query tambah saldo
            $saldoStats = DB::table('tambah_saldo')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->when($isFiltering, function ($q) use ($startDate, $endDate) {
                    return $q->whereDate('tanggal', '>=', $startDate)
                        ->whereDate('tanggal', '<=', $endDate);
                }, fn($q) => $q->whereDate('tanggal', today()))
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

            $currentSaldo = \App\Models\Perusahaan::query()->find($tenantId)->saldo ?? 0;
            $periodLabel = $isFiltering ? '' : ' (Hari Ini)';
            $cashierName = $tenant->kasir?->name ?? $tenant->nama_kasir ?? \Filament\Facades\Filament::auth()->user()->name;

            // Saldo Awal Hari Ini (Saldo Sekarang - Net Transaksi Hari Ini)
            $saldoAwalHariIni = $currentSaldo - ($totalIncoming - $totalExpenditure);

            return [
                Stat::make($tenant->name, \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y'))
                        ->description('Kasir: ' . $cashierName)
                        ->descriptionIcon('heroicon-m-user-circle')
                        ->color('primary'),

                Stat::make('Saldo Kas', money($currentSaldo, 'IDR'))
                    ->description(sprintf(
                        'Awal: %s | M: +%s | K: -%s',
                        money($saldoAwalHariIni, 'IDR', true),
                        money($totalIncoming, 'IDR', true),
                        money($totalExpenditure, 'IDR', true)
                    ))
                    ->descriptionIcon('heroicon-m-wallet')
                    ->color($currentSaldo < 0 ? 'danger' : 'success'),

                Stat::make('Transaksi DO' . $periodLabel, (int)$doStats->count . ' Transaksi')
                    ->description(sprintf(
                        'Bruto: %s | Potongan: %s | Tunai:%d, Transfer:%d, Belum:%d',
                        money($doStats->total_bruto, 'IDR'),
                        money($doStats->total_potong_hutang + $doStats->total_bongkar + $doStats->total_lain, 'IDR'),
                        $doStats->tunai_count,
                        $doStats->transfer_count,
                        $doStats->belum_count
                    ))
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('info'),

                Stat::make('Total Tonase' . $periodLabel, number_format($doStats->total_tonase, 0, ',', '.') . ' Kg')
                    ->description('Total volume buah masuk')
                    ->descriptionIcon('heroicon-m-scale')
                    ->color('warning'),

                Stat::make('Uang Masuk' . $periodLabel, new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-success-600 text-white font-bold text-base shadow-sm">
                            ' . money($totalIncoming, 'IDR') . '
                        </div>
                    '))
                    ->description(sprintf(
                        'Tmb Saldo: %s | Ops: %s | Htg Supir: %s',
                        money($saldoStats->total_tambah_saldo, 'IDR'),
                        money((float)$opStats->total_pemasukan - (float)$opStats->htg_supir, 'IDR'),
                        money($opStats->htg_supir, 'IDR')
                    ))
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success'),

                Stat::make('Pengeluaran' . $periodLabel, new \Illuminate\Support\HtmlString('
                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-danger-600 text-white font-bold text-base shadow-sm">
                            ' . money($totalExpenditure, 'IDR') . '
                        </div>
                    '))
                    ->description(sprintf(
                        'Tunai: %s | Ops: %s | Blj Kasir: %s',
                        money($doStats->total_bayar_tunai + $doStats->total_bayar_mixed, 'IDR'),
                        money((float)$opStats->total_pengeluaran + (float)$doStats->total_bongkar + (float)$doStats->total_lain - (float)$opStats->belanja_kasir, 'IDR'),
                        money($opStats->belanja_kasir, 'IDR')
                    ))
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger'),
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
