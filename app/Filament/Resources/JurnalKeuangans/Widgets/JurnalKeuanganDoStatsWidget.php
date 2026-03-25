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
    protected ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'hari_ini'; // Default tab

    // Initialize default dates on mount
    // Initialize default dates & tab saat mount
    public function mount(): void
    {
        $this->startDate = today(); 
        $this->endDate = today();
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
    #[On('tab-changed')]
    public function handleTabChanged($tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('filter-laporan')]
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
            $selisih = $data['total_pemasukan'] - $data['total_pengeluaran'];

            return [
                // Stat 1: Selisih Kas (Periode)
                Stat::make('SELISIH KAS (PERIODE)', 'Rp ' . number_format($selisih, 0, ',', '.'))
                    ->description($selisih >= 0 ? 'Surplus' : 'Defisit')
                    ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($selisih >= 0 ? 'success' : 'danger'),

                // Stat 2: Total Saldo / Uang Masuk
                Stat::make('TOTAL SALDO/UANG MASUK', 'Rp ' . number_format($data['total_pemasukan'], 0, ',', '.'))
                    ->description(sprintf(
                        "Total %d Transaksi (tunai: %d, transfer: %d, cair di luar: %d, belum dibayar: %d)",
                        $data['count_do_unique'],
                        $data['count_do_tunai'],
                        $data['count_do_transfer'],
                        $data['count_do_cair_luar'],
                        $data['count_do_belum_bayar']
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                // Stat 3: Pengeluaran / Uang Keluar
                Stat::make('PENGELUARAN/UANG KELUAR', 'Rp ' . number_format($data['total_pengeluaran'], 0, ',', '.'))
                    ->description(sprintf(
                        "Total DO: Rp %s | Total Operasional: Rp %s",
                        number_format($data['total_pengeluaran_do'], 0, ',', '.'),
                        number_format($data['total_pengeluaran_op'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('danger'),

                // Stat 4: Saldo Akhir Perusahaan
                Stat::make('SALDO AKHIR PERUSAHAAN', 'Rp ' . number_format($perusahaan?->saldo ?? 0, 0, ',', '.'))
                    ->description(sprintf("Total: %d Item Jurnal", $data['total_transaksi']))
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('info')
            ];
        } catch (\Exception $e) {
            Log::error('Widget Error:', ['error' => $e->getMessage()]);
            return [Stat::make('Error', 'Gagal memuat statistik')];
        }
    }

    // Get filtered statistics
    private function getFilteredStats(): array
    {
        $query = JurnalKeuangan::query();

        // Tab specific filtering
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
            
            // Logika baru (Sesuai Gambar Ref: Defisit -94jt)
            // Header Pengeluaran = Gross DO (Pembelian Buah) + Semua Pengeluaran Operasional (Jurnal)
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND sub_kategori = "Pembelian Buah" THEN nominal ELSE 0 END), 0) as total_pengeluaran_do'),
            DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" AND (kategori != "DO" OR sub_kategori != "Pembelian Buah") THEN nominal ELSE 0 END), 0) as total_pengeluaran_op'),
            
            // Header Uang Masuk = Hanya Potongan DO (Hutang) + Non-Tunai DO (Transfer/Cair Luar)
            // Ini untuk menunjukkan defisit operasional DO per hari (-94jt)
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND sub_kategori = "Potong Hutang" THEN nominal ELSE 0 END), 0) as masuk_do_hutang'),
            DB::raw('COALESCE(SUM(CASE WHEN kategori = "DO" AND cara_pembayaran IN ("transfer", "cair di luar") AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as masuk_do_nontunai'),

            // Counts for DO Unique (19 Transaksi)
            DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" THEN referensi_id END) as count_do_unique'),
            DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "tunai" THEN referensi_id END) as count_do_tunai'),
            DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "transfer" THEN referensi_id END) as count_do_transfer'),
            DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND cara_pembayaran = "cair di luar" THEN referensi_id END) as count_do_cair_luar'),
            DB::raw('COUNT(DISTINCT CASE WHEN sumber_transaksi = "DO" AND (cara_pembayaran IS NULL OR cara_pembayaran = "") THEN referensi_id END) as count_do_belum_bayar'),
        ])->first();

        // Formula Sinkronisasi Dashboard & Laporan (Step Id: 1761 Image Ref)
        // Uang Masuk Header = Potongan Hutang + Transfer (agar Selisih matches -94m)
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
