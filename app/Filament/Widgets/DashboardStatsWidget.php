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
            $today = Carbon::today();
            
            // 1. Ambil Statistik Transaksi DO dalam 1 query
            $doStats = TransaksiDo::query()
                ->where('perusahaan_id', $tenant->id)
                ->whereDate('tanggal', $today)
                ->selectRaw('
                    SUM(sub_total) as total_bruto, 
                    COUNT(*) as jumlah_transaksi,
                    SUM(CASE WHEN cara_bayar = "tunai" THEN 1 ELSE 0 END) as tunai_count,
                    SUM(CASE WHEN cara_bayar = "transfer" THEN 1 ELSE 0 END) as transfer_count
                ')
                ->first();

            // 2. Ambil Statistik Jurnal Keuangan dalam 1 query
            $jurnalStats = JurnalKeuangan::query()
                ->where('perusahaan_id', $tenant->id)
                ->whereDate('tanggal', $today)
                ->selectRaw('
                    SUM(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as total_operasional,
                    SUM(CASE WHEN cara_pembayaran = "transfer" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as total_transfer,
                    SUM(CASE WHEN sub_kategori = "Potong Hutang" THEN nominal ELSE 0 END) as potong_hutang
                ')
                ->first();

            $totalDoBruto = (float) ($doStats->total_bruto ?? 0);
            $totalOperasional = (float) ($jurnalStats->total_operasional ?? 0);
            $pengeluaranTotal = $totalDoBruto + $totalOperasional;

            $totalTransfer = (float) ($jurnalStats->total_transfer ?? 0);
            $potongHutang = (float) ($jurnalStats->potong_hutang ?? 0);
            $uangMasukTotal = $totalTransfer + $potongHutang;

            $selisihKas = $uangMasukTotal - $pengeluaranTotal;
            $displaySaldoAkhir = (float) $tenant->saldo;

            $jumlahTransaksi = (int) ($doStats->jumlah_transaksi ?? 0);
            
        return [
            Stat::make('SELISIH KAS (PERIODE)', new \Illuminate\Support\HtmlString('<div class="text-base font-bold">Rp ' . number_format($selisihKas, 0, ',', '.') . '</div>'))
                ->description($selisihKas < 0 ? 'Peringatan: Pengeluaran melebihi pemasukan hari ini.' : 'Total Uang Masuk - Total Pengeluaran')
                ->icon('heroicon-m-scale')
                ->color($selisihKas >= 0 ? 'success' : 'danger'),

            Stat::make('TOTAL SALDO/UANG MASUK', new \Illuminate\Support\HtmlString('<div class="text-base font-bold">Rp ' . number_format($uangMasukTotal, 0, ',', '.') . '</div>'))
                ->description(sprintf(
                    "Transfer: Rp %s | Hutang: Rp %s",
                    number_format($totalTransfer, 0, ',', '.'),
                    number_format($potongHutang, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-down-circle')
                ->color('success'),

            Stat::make('PENGELUARAN/UANG KELUAR', new \Illuminate\Support\HtmlString('<div class="text-base font-bold">Rp ' . number_format($pengeluaranTotal, 0, ',', '.') . '</div>'))
                ->description(sprintf(
                    "Total DO: Rp %s | Operasional: Rp %s",
                    number_format($totalDoBruto, 0, ',', '.'),
                    number_format($totalOperasional, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-up-circle')
                ->color('danger'),

            Stat::make('SALDO AKHIR PERUSAHAAN', new \Illuminate\Support\HtmlString('<div class="text-base font-bold">Rp ' . number_format($displaySaldoAkhir, 0, ',', '.') . '</div>'))
                ->description($displaySaldoAkhir < 0 ? 'Peringatan: Saldo minus (Aset kas kosong).' : "Saldo Real (Termasuk Profit Biaya)")
                ->icon('heroicon-m-banknotes')
                ->color($displaySaldoAkhir >= 0 ? 'success' : 'danger'),
        ];
        });
    }
}
