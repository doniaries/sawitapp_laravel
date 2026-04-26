<?php

namespace App\Filament\Resources\Perusahaans\Widgets;

use App\Models\{Perusahaan, User};
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\{DB, Cache};
use Livewire\Attributes\On;

class PerusahaanStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            $perusahaan = Filament::getTenant();

            if (!$perusahaan) {
                return [];
            }
            
            $cacheKey = "perusahaan-stats-tenant-{$perusahaan->id}";

            return Cache::remember($cacheKey, 60, function () use ($perusahaan) {
                // 1. Get kasir names in one query
                $kasirNames = User::where('perusahaan_id', $perusahaan->id)
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'kasir'))
                    ->pluck('name')
                    ->whenEmpty(fn() => collect(['Belum ada kasir']))
                    ->join(', ');

                // 2. Get last saldo addition and aggregate sums in one pass if possible, 
                // but lastSaldo needs orderBy, so we'll do 2 queries.
                
                $lastSaldo = DB::table('jurnal_keuangan')
                    ->where('perusahaan_id', $perusahaan->id)
                    ->whereNull('deleted_at')
                    ->where('kategori', 'Saldo')
                    ->where('sub_kategori', 'Tambah Saldo')
                    ->orderBy('tanggal', 'desc')
                    ->select(['nominal', 'tanggal'])
                    ->first();

                $lastSaldoInfo = $lastSaldo ?
                    "Terakhir tambah: Rp " . number_format($lastSaldo->nominal, 0, ',', '.') .
                    " (" . date('d/m/Y', strtotime($lastSaldo->tanggal)) . ")" :
                    "Belum ada penambahan saldo";

                // 3. Combine Pemasukan and Pengeluaran sums
                $jurnalSums = DB::table('jurnal_keuangan')
                    ->where('perusahaan_id', $perusahaan->id)
                    ->whereNull('deleted_at')
                    ->where('mempengaruhi_kas', true)
                    ->selectRaw('
                        SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran
                    ')->first();

                $saldoAkhir = (float)$perusahaan->saldo;

                return [
                    Stat::make('Saldo Perusahaan', new \Illuminate\Support\HtmlString('<div class="text-sm font-bold text-white bg-blue-600 dark:bg-blue-500 px-2 py-1 rounded shadow-sm inline-block">Rp ' . number_format($saldoAkhir, 0, ',', '.') . '</div>'))
                        ->description($lastSaldoInfo)
                        ->descriptionIcon('heroicon-m-banknotes')
                        ->color($this->getSaldoColor($saldoAkhir)),

                    Stat::make($perusahaan->name, $perusahaan->pimpinan ?? 'Pimpinan belum diatur')
                        ->description("Kasir: {$kasirNames}")
                        ->descriptionIcon('heroicon-m-user-group')
                        ->color('info'),

                    Stat::make(
                        'Total Pemasukan',
                        new \Illuminate\Support\HtmlString('<div class="text-base font-bold text-blue-600 dark:text-blue-400">Rp ' . number_format($jurnalSums->total_pemasukan ?? 0, 0, ',', '.') . '</div>')
                    )
                        ->description(
                            'Total Pengeluaran: Rp ' . number_format($jurnalSums->total_pengeluaran ?? 0, 0, ',', '.')
                        )
                        ->descriptionIcon('heroicon-m-arrow-path')
                        ->color('success'),
                ];
            });
        } catch (\Exception $e) {
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
            $saldo > 100000000 => 'success',
            $saldo > 50000000 => 'info',
            $saldo > 10000000 => 'warning',
            $saldo > 0 => 'gray',
            default => 'danger'
        };
    }

    #[On(['refresh-widget', 'saldo-updated', 'laporan-created', 'laporan-deleted'])]
    public function refresh(): void
    {
        $tenantId = Filament::getTenant()?->id;
        if ($tenantId) {
            Cache::forget("perusahaan-stats-tenant-{$tenantId}");
        }
    }

}
