<?php

namespace App\Filament\Resources\JurnalKeuangans\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use App\Models\{JurnalKeuangan, Perusahaan};
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class JurnalKeuanganDoStatsWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'hari_ini';

    public function mount(): void
    {
        $this->startDate = today(); 
        $this->endDate = today();
    }

    #[On(['refresh-widgets', 'saldo-updated', 'transaksi-created', 'transaksi-deleted'])]
    public function refresh(): void
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        if ($tenantId) {
            Cache::forget("jurnal-do-stats-tenant-{$tenantId}-tab-{$this->activeTab}");
        }
    }

    public function getHeading(): ?string
    {
        return 'Ringkasan Laporan Keuangan';
    }

    protected function getStats(): array
    {
        try {
            $tenant = \Filament\Facades\Filament::getTenant();
            if (!$tenant) return [];

            $data = $this->getFilteredStats();
            $selisih = $data['total_pemasukan'] - $data['total_pengeluaran'];

            $blueBadge = 'text-sm font-bold text-white bg-blue-600 dark:bg-blue-500 px-2 py-1 rounded shadow-sm inline-block';
            $greenBadge = 'text-sm font-bold text-white bg-green-600 dark:bg-green-500 px-2 py-1 rounded shadow-sm inline-block';
            $redBadge = 'text-sm font-bold text-white bg-red-600 dark:bg-red-500 px-2 py-1 rounded shadow-sm inline-block';

            return [
                Stat::make('SELISIH KAS (PERIODE)', new \Illuminate\Support\HtmlString('<div class="' . ($selisih >= 0 ? $greenBadge : $redBadge) . '">Rp ' . number_format($selisih, 0, ',', '.') . '</div>'))
                    ->description($selisih >= 0 ? 'Surplus' : 'Defisit')
                    ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($selisih >= 0 ? 'success' : 'danger'),

                Stat::make('TOTAL SALDO/UANG MASUK', new \Illuminate\Support\HtmlString('<div class="' . $greenBadge . '">Rp ' . number_format($data['total_pemasukan'], 0, ',', '.') . '</div>'))
                    ->description(sprintf(
                        "Total %d Transaksi (tunai: %d, transfer: %d, cair: %d, belum: %d)",
                        $data['count_do_unique'],
                        $data['count_do_tunai'],
                        $data['count_do_transfer'],
                        $data['count_do_cair_luar'],
                        $data['count_do_belum_bayar']
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                Stat::make('PENGELUARAN/UANG KELUAR', new \Illuminate\Support\HtmlString('<div class="' . $redBadge . '">Rp ' . number_format($data['total_pengeluaran'], 0, ',', '.') . '</div>'))
                    ->description(sprintf(
                        "Total DO: Rp %s | Operasional: Rp %s",
                        number_format($data['total_pengeluaran_do'], 0, ',', '.'),
                        number_format($data['total_pengeluaran_op'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('danger'),

                Stat::make('SALDO AKHIR PERUSAHAAN', new \Illuminate\Support\HtmlString('<div class="' . $blueBadge . '">Rp ' . number_format((float)$tenant->saldo, 0, ',', '.') . '</div>'))
                    ->description(sprintf("Total: %d Item Jurnal", $data['total_transaksi']))
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('info')
            ];
        } catch (\Exception $e) {
            Log::error('Widget Error:', ['error' => $e->getMessage()]);
            return [Stat::make('Error', 'Gagal memuat statistik')];
        }
    }

    private function getFilteredStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "jurnal-do-stats-tenant-{$tenantId}-tab-{$this->activeTab}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($tenantId) {
            $query = JurnalKeuangan::query()->where('perusahaan_id', $tenantId);

            match ($this->activeTab) {
                'hari_ini' => $query->whereDate('tanggal', today()),
                'kemarin' => $query->whereDate('tanggal', now()->subDay()),
                'bulan_ini' => $query->whereMonth('tanggal', today()->month)->whereYear('tanggal', today()->year),
                'tahun_ini' => $query->whereYear('tanggal', today()->year),
                'tunai' => $query->where('cara_pembayaran', 'tunai'),
                'transfer' => $query->where('cara_pembayaran', 'transfer'),
                'cair_luar' => $query->where('cara_pembayaran', 'cair di luar'),
                'belum_dibayar' => $query->where('cara_pembayaran', 'belum dibayar'),
                'semua' => $query->whereBetween('tanggal', [
                    $this->startDate->startOfDay(),
                    $this->endDate->endOfDay()
                ]),
                default => $query->whereBetween('tanggal', [
                    $this->startDate->startOfDay(),
                    $this->endDate->endOfDay()
                ]),
            };

            $stats = $query->select([
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND sub_kategori = "Pembelian Buah" THEN nominal ELSE 0 END), 0) as total_pengeluaran_do'),
                DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" AND (kategori != "DO" OR sub_kategori != "Pembelian Buah") THEN nominal ELSE 0 END), 0) as total_pengeluaran_op'),
                DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND sub_kategori = "Potong Hutang" THEN nominal ELSE 0 END), 0) as masuk_do_hutang'),
                DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND cara_pembayaran IN ("transfer", "cair di luar") AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as masuk_do_nontunai'),
                DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" THEN referensi_id END) as count_do_unique'),
                DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "tunai" THEN referensi_id END) as count_do_tunai'),
                DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "transfer" THEN referensi_id END) as count_do_transfer'),
                DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "cair di luar" THEN referensi_id END) as count_do_cair_luar'),
                DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND (cara_pembayaran IS NULL OR cara_pembayaran = "") THEN referensi_id END) as count_do_belum_bayar'),
            ])->first();

            $totalPemasukanHeader = (float) $stats->masuk_do_hutang + (float) $stats->masuk_do_nontunai;
            $totalPengeluaranHeader = (float) $stats->total_pengeluaran_do + (float) $stats->total_pengeluaran_op;

            return [
                'total_transaksi' => (int) $stats->total_transaksi,
                'total_pemasukan' => $totalPemasukanHeader,
                'total_pengeluaran' => $totalPengeluaranHeader,
                'total_pengeluaran_do' => (float) $stats->total_pengeluaran_do,
                'total_pengeluaran_op' => (float) $stats->total_pengeluaran_op,
                'count_do_unique' => (int) $stats->count_do_unique,
                'count_do_tunai' => (int) $stats->count_do_tunai,
                'count_do_transfer' => (int) $stats->count_do_transfer,
                'count_do_cair_luar' => (int) $stats->count_do_cair_luar,
                'count_do_belum_bayar' => (int) $stats->count_do_belum_bayar,
            ];
        });
    }


    // Determine saldo color based on value
    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 1000000000 => 'success',
            $saldo > 100000000 => 'info',
            $saldo > 0 => 'warning',
            default => 'danger'
        };
    }

    // Create DO transaction stat
    private function createDOStat($data): Stat
    {
        return Stat::make('Transaksi DO', function () use ($data) {
            return sprintf(
                "Masuk: Rp %s | Keluar: Rp %s",
                number_format($data['do_in'], 0, ',', '.'),
                number_format($data['do_out'], 0, ',', '.')
            );
        })
            ->description(sprintf(
                "Upah: Rp %s\nBiaya: Rp %s\nHutang: Rp %s",
                number_format($data['upah_bongkar'], 0, ',', '.'),
                number_format($data['biaya_lain'], 0, ',', '.'),
                number_format($data['bayar_hutang'], 0, ',', '.')
            ))
            ->descriptionIcon('heroicon-m-document-text')
            ->color('primary');
    }

    // Create operational transaction stat
    private function createOperasionalStat($data): Stat
    {
        return Stat::make('Transaksi Operasional', function () use ($data) {
            return sprintf(
                "Masuk: Rp %s | Keluar: Rp %s",
                number_format($data['op_in'], 0, ',', '.'),
                number_format($data['op_out'], 0, ',', '.')
            );
        })
            ->description(sprintf(
                "tunai: Rp %s\ntransfer: Rp %s",
                number_format($data['tunai'], 0, ',', '.'),
                number_format($data['transfer'], 0, ',', '.')
            ))
            ->descriptionIcon('heroicon-m-banknotes')
            ->color($data['op_in'] > $data['op_out'] ? 'success' : 'danger');
    }

    // Create mutation summary stat
    private function createMutasiStat(array $data): Stat
    {
        $selisih = $data['total_in'] - $data['total_out'];

        return Stat::make('Total Mutasi', sprintf("Rp %s", number_format(abs($selisih), 0, ',', '.')))
            ->description(sprintf(
                "Total Masuk: Rp %s\nTotal Keluar: Rp %s",
                number_format($data['total_in'], 0, ',', '.'),
                number_format($data['total_out'], 0, ',', '.')
            ))
            ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($selisih >= 0 ? 'success' : 'danger');
    }




    private function getTransactionDescription(array $data): string
    {
        return sprintf(
            "Total Transaksi: %d\nFilter: %s",
            $data['total_transaksi'],
            ucfirst($this->activeTab)
        );
    }

    private function getPaymentDescription(array $data): string
    {
        return sprintf(
            "tunai: Rp %s\ntransfer: Rp %s\nCair Luar: Rp %s",
            number_format($data['total_tunai'], 0, ',', '.'),
            number_format($data['total_transfer'], 0, ',', '.'),
            number_format($data['total_cair_luar'], 0, ',', '.')
        );
    }
    private function getFilterDescription(): string
    {
        return sprintf(
            "Filter: %s s/d %s",
            $this->startDate->format('d/m/Y'),
            $this->endDate->format('d/m/Y')
        );
    }

    // Listen for date filter updates
    #[On('filterDate')]
    public function updateDateFilter($startDate, $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
