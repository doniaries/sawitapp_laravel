<?php

namespace App\Filament\Resources\LaporanKeuanganResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use App\Models\{LaporanKeuangan, Perusahaan};
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class LaporanKeuanganDoStatsWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'semua'; // Default tab

    // Initialize default dates on mount
    // Initialize default dates & tab saat mount
    public function mount(): void
    {
        $this->startDate = now()->subDays(30); // Default 30 hari terakhir
        $this->endDate = now();
    }

    //update stats saldo
    #[On(['refresh-widgets', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getFilteredStats();
    }

    // Method untuk heading widget
    public function getHeading(): ?string
    {
        return 'Ringkasan Laporan Keuangan';
    }

    // Listen untuk event filter
    #[On(['filter-laporan', 'tab-changed'])]
    public function handleFilter($data = []): void
    {
        if (isset($data['startDate'])) {
            $this->startDate = Carbon::parse($data['startDate']);
        }
        if (isset($data['endDate'])) {
            $this->endDate = Carbon::parse($data['endDate']);
        }
        if (isset($data['tab'])) {
            $this->activeTab = $data['tab'];
        }
    }

    // Main stats getter
    protected function getStats(): array
    {
        try {
            $data = $this->getFilteredStats();
            $perusahaan = Perusahaan::first();

            return [
                // Stat 1: Saldo & Overview
                Stat::make('Saldo Perusahaan', fn() => 'Rp ' . number_format($perusahaan?->saldo ?? 0, 0, ',', '.'))
                    ->description(sprintf(
                        "Total: %d Transaksi\nDO: %d | Operasional: %d",
                        $data['total_transaksi'],
                        $data['count_do'],
                        $data['count_operasional']
                    ))
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color($this->getSaldoColor($perusahaan?->saldo ?? 0)),

                // Stat 2: Transaksi tunai
                Stat::make('Pembayaran tunai', sprintf(
                    "Rp %s (%d Trans.)",
                    number_format($data['total_tunai'], 0, ',', '.'),
                    $data['count_tunai']
                ))
                    ->description(sprintf(
                        "DO: Rp %s (%d)\nOperasional: Rp %s (%d)\nMempengaruhi Kas: Ya",
                        number_format($data['tunai_do'], 0, ',', '.'),
                        $data['count_tunai_do'],
                        number_format($data['tunai_operasional'], 0, ',', '.'),
                        $data['count_tunai_operasional']
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                // Stat 3: Non-tunai (transfer & Cair Luar)
                Stat::make('Pembayaran Non-tunai', sprintf(
                    "Rp %s (%d Trans.)",
                    number_format($data['total_non_tunai'], 0, ',', '.'),
                    $data['count_non_tunai']
                ))
                    ->description(sprintf(
                        "transfer: Rp %s (%d)\nCair Luar: Rp %s (%d)\nMempengaruhi Kas: Tidak",
                        number_format($data['total_transfer'], 0, ',', '.'),
                        $data['count_transfer'],
                        number_format($data['total_cair_luar'], 0, ',', '.'),
                        $data['count_cair_luar']
                    ))
                    ->descriptionIcon('heroicon-m-credit-card')
                    ->color('warning'),

                // Stat 4: Ringkasan Operasional
                Stat::make('Ringkasan Operasional', sprintf(
                    "Rp %s (%d Trans.)",
                    number_format($data['total_operasional'], 0, ',', '.'),
                    $data['count_operasional']
                ))
                    ->description(sprintf(
                        "Masuk: Rp %s (%d)\nKeluar: Rp %s (%d)\nBayar Hutang: Rp %s",
                        number_format($data['operasional_in'], 0, ',', '.'),
                        $data['count_operasional_in'],
                        number_format($data['operasional_out'], 0, ',', '.'),
                        $data['count_operasional_out'],
                        number_format($data['bayar_hutang_op'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-clipboard-document-list')
                    ->color('info')
            ];
        } catch (\Exception $e) {
            Log::error('Widget Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan memuat data')
                    ->description('Silakan refresh halaman')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
            ];
        }
    }

    // Get filtered statistics
    private function getFilteredStats(): array
    {
        $query = LaporanKeuangan::query();

        // Apply time-based filter from tab
        match ($this->activeTab) {
            'hari_ini' => $query->whereDate('tanggal', now()),
            'bulan_ini' => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year),
            'tahun_ini' => $query->whereYear('tanggal', now()->year),
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
            // Basic counts
            DB::raw('COUNT(*) as total_transaksi'),
            DB::raw('COUNT(CASE WHEN kategori = "DO" THEN 1 END) as count_do'),
            DB::raw('COUNT(CASE WHEN kategori = "Operasional" THEN 1 END) as count_operasional'),

            // tunai transactions
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "tunai" THEN 1 END) as count_tunai'),
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "tunai" AND kategori = "DO" THEN 1 END) as count_tunai_do'),
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "tunai" AND kategori = "Operasional" THEN 1 END) as count_tunai_operasional'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "tunai" THEN nominal ELSE 0 END), 0) as total_tunai'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "tunai" AND kategori = "DO" THEN nominal ELSE 0 END), 0) as tunai_do'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "tunai" AND kategori = "Operasional" THEN nominal ELSE 0 END), 0) as tunai_operasional'),

            // Non-tunai transactions
            DB::raw('COUNT(CASE WHEN cara_pembayaran != "tunai" THEN 1 END) as count_non_tunai'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran != "tunai" THEN nominal ELSE 0 END), 0) as total_non_tunai'),

            // transfer details
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "transfer" THEN 1 END) as count_transfer'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "transfer" THEN nominal ELSE 0 END), 0) as total_transfer'),

            // Cair Luar details
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "cair di luar" THEN 1 END) as count_cair_luar'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "cair di luar" THEN nominal ELSE 0 END), 0) as total_cair_luar'),

            // Operasional details
            DB::raw('COUNT(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pemasukan" THEN 1 END) as count_operasional_in'),
            DB::raw('COUNT(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pengeluaran" THEN 1 END) as count_operasional_out'),
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END), 0) as operasional_in'),
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as operasional_out'),
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "Operasional" THEN nominal ELSE 0 END), 0) as total_operasional'),
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "Operasional" AND sub_kategori = "Bayar Hutang" THEN nominal ELSE 0 END), 0) as bayar_hutang_op')
        ])->first();

        return [
            'total_transaksi' => (int) $stats->total_transaksi,
            'count_do' => (int) $stats->count_do,
            'count_operasional' => (int) $stats->count_operasional,

            'count_tunai' => (int) $stats->count_tunai,
            'count_tunai_do' => (int) $stats->count_tunai_do,
            'count_tunai_operasional' => (int) $stats->count_tunai_operasional,
            'total_tunai' => (float) $stats->total_tunai,
            'tunai_do' => (float) $stats->tunai_do,
            'tunai_operasional' => (float) $stats->tunai_operasional,

            'count_non_tunai' => (int) $stats->count_non_tunai,
            'total_non_tunai' => (float) $stats->total_non_tunai,
            'count_transfer' => (int) $stats->count_transfer,
            'total_transfer' => (float) $stats->total_transfer,
            'count_cair_luar' => (int) $stats->count_cair_luar,
            'total_cair_luar' => (float) $stats->total_cair_luar,

            'count_operasional_in' => (int) $stats->count_operasional_in,
            'count_operasional_out' => (int) $stats->count_operasional_out,
            'operasional_in' => (float) $stats->operasional_in,
            'operasional_out' => (float) $stats->operasional_out,
            'total_operasional' => (float) $stats->total_operasional,
            'bayar_hutang_op' => (float) $stats->bayar_hutang_op
        ];
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
