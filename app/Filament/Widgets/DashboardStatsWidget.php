<?php

namespace App\Filament\Widgets;

use App\Models\{Perusahaan, Penjual, TransaksiDo, JurnalKeuangan};
use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use App\Filament\Resources\Supirs\SupirResource;
use App\Filament\Resources\Penjuals\PenjualResource;
use App\Filament\Resources\Pekerjas\PekerjaResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

class DashboardStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    public static function shouldRegister(): bool
    {
        return \App\Providers\Filament\AdminPanelProvider::$dashboardWidgets['stats'] ?? true;
    }

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $tenant = Filament::getTenant();
        $cacheKey = "dashboard_stats_tenant_{$tenant->id}_" . now()->format('YmdH');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($tenant) {
            $today = now();
            
            // 2. Daily Income Breakdown

        // 1. Hitung Pengeluaran Hari Ini (DO Bruto + Operasional)
        $totalDoBruto = (float) TransaksiDo::query()
            ->whereDate('tanggal', Carbon::today())
            ->sum('sub_total');
        
        $totalOperasional = (float) JurnalKeuangan::query()
            ->whereDate('tanggal', Carbon::today())
            ->where('kategori', 'Operasional')
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');
        
        $pengeluaranTotal = $totalDoBruto + $totalOperasional;

        // 2. Hitung Uang Masuk Hari Ini (Transfer + Potong Hutang) - ABAIKAN BIAYA (Sesuai Gambar)
        $totalTransfer = (float) JurnalKeuangan::query()
            ->whereDate('tanggal', Carbon::today())
            ->where('cara_pembayaran', 'transfer')
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');

        $potongHutang = (float) JurnalKeuangan::query()
            ->whereDate('tanggal', Carbon::today())
            ->where('sub_kategori', 'Potong Hutang')
            ->sum('nominal');

        $uangMasukTotal = $totalTransfer + $potongHutang;

        // 3. Selisih Kas (Periode)
        $selisihKas = $uangMasukTotal - $pengeluaranTotal;

        // 4. Saldo Akhir (Kita tampilkan saldo real namun beri penjelasan jika ada selisih profit biaya)
        $currentBalance = (float) $tenant->saldo;
        
        // Agar angka di widget PAS 953.470 (Sesuai Gambar), kita hitung mundur dari saldo awal simulasi jika sedang sesi hari ini
        $displaySaldoAkhir = $currentBalance;
        // Opsional: Jika user ingin angkanya MATI sesuai gambar:
        // $displaySaldoAkhir = 95215000 + $selisihKas; 

        // Info tambahan untuk deskripsi
        $jumlahTransaksi = TransaksiDo::query()
            ->whereDate('tanggal', Carbon::today())
            ->count();
            
        $tunaiCount = TransaksiDo::query()->whereDate('tanggal', Carbon::today())->where('cara_bayar', 'tunai')->count();
        $transferCount = TransaksiDo::query()->whereDate('tanggal', Carbon::today())->where('cara_bayar', 'transfer')->count();

        return [
            Stat::make('SELISIH KAS (PERIODE)', 'Rp ' . number_format($selisihKas, 0, ',', '.'))
                ->description('Total Uang Masuk - Total Pengeluaran')
                ->icon('heroicon-m-scale')
                ->color($selisihKas >= 0 ? 'success' : 'danger'),

            Stat::make('TOTAL SALDO/UANG MASUK', 'Rp ' . number_format($uangMasukTotal, 0, ',', '.'))
                ->description(sprintf(
                    "Transfer: Rp %s | Hutang: Rp %s",
                    number_format($totalTransfer, 0, ',', '.'),
                    number_format($potongHutang, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-down-circle')
                ->color('success'),

            Stat::make('PENGELUARAN/UANG KELUAR', 'Rp ' . number_format($pengeluaranTotal, 0, ',', '.'))
                ->description(sprintf(
                    "Total DO: Rp %s | Operasional: Rp %s",
                    number_format($totalDoBruto, 0, ',', '.'),
                    number_format($totalOperasional, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-up-circle')
                ->color('danger'),

            Stat::make('SALDO AKHIR PERUSAHAAN', 'Rp ' . number_format($displaySaldoAkhir, 0, ',', '.'))
                ->description("Menampilkan Saldo Real (Termasuk Profit Biaya)")
                ->icon('heroicon-m-banknotes')
                ->color($displaySaldoAkhir >= 0 ? 'success' : 'danger'),
        ];
        });
    }
}
