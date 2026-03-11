<?php

namespace App\Filament\Resources\PerusahaanResource\Widgets;

use App\Models\{Perusahaan, User};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\{DB, Cache};
use Livewire\Attributes\On;

class PerusahaanStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            return Cache::remember('perusahaan-stats', 60, function () {
                // Get perusahaan data
                $perusahaan = Perusahaan::first();

                // Get active kasir
                $kasir = User::where('is_active', true)
                    ->whereNotNull('perusahaan_id')
                    ->get(['name']);

                $kasirNames = $kasir->pluck('name')->join(', ');

                // Get last saldo addition
                $lastSaldo = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('kategori', 'Saldo')
                    ->where('sub_kategori', 'Tambah Saldo')
                    ->orderBy('tanggal', 'desc')
                    ->first();

                $lastSaldoInfo = $lastSaldo ?
                    "Terakhir tambah: Rp " . number_format($lastSaldo->nominal, 0, ',', '.') .
                    " (" . date('d/m/Y', strtotime($lastSaldo->tanggal)) . ")" :
                    "Belum ada penambahan saldo";

                // Calculate total pemasukan
                $totalPemasukan = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('jenis_transaksi', 'Pemasukan')
                    ->where('mempengaruhi_kas', true)
                    ->sum('nominal');

                // Calculate total pengeluaran
                $totalPengeluaran = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('jenis_transaksi', 'Pengeluaran')
                    ->where('mempengaruhi_kas', true)
                    ->sum('nominal');

                // Calculate saldo
                $saldoAkhir = $perusahaan->saldo;

                // Log calculations for debugging
                \Log::info('Widget Stats Calculation:', [
                    'total_pemasukan' => $totalPemasukan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_sistem' => $saldoAkhir,
                    'saldo_kalkulasi' => $totalPemasukan - $totalPengeluaran
                ]);

                return [
                    // Saldo from perusahaans table
                    Stat::make('Saldo Perusahaan', 'Rp ' . number_format($saldoAkhir, 0, ',', '.'))
                        ->description($lastSaldoInfo)
                        ->descriptionIcon('heroicon-m-banknotes')
                        ->color($this->getSaldoColor($saldoAkhir)),

                    // Pimpinan as main title with company name below
                    Stat::make($perusahaan->name, $perusahaan->pimpinan)
                        ->description("Kasir: {$kasirNames}")
                        ->descriptionIcon('heroicon-m-user-group')
                        ->color('info'),

                    // Transaction summary
                    Stat::make(
                        'Total Pemasukan',
                        'Rp ' . number_format($totalPemasukan, 0, ',', '.')
                    )
                        ->description(
                            'Total Pengeluaran: Rp ' . number_format($totalPengeluaran, 0, ',', '.')
                        )
                        ->descriptionIcon('heroicon-m-arrow-path')
                        ->color('success'),
                ];
            });
        } catch (\Exception $e) {
            \Log::error('PerusahaanStatsWidget Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('Error', 'Gagal memuat data')
                    ->description('Silakan refresh halaman')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }

    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 100000000 => 'success',  // > 100jt
            $saldo > 50000000 => 'info',      // > 50jt
            $saldo > 10000000 => 'warning',   // > 10jt
            $saldo > 0 => 'gray',             // > 0
            default => 'danger'                // <= 0
        };
    }

    #[On(['refresh-widget', 'saldo-updated', 'laporan-created', 'laporan-deleted'])]
    public function refresh(): void
    {
        Cache::forget('perusahaan-stats');
        $this->getStats();
    }
}
