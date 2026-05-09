<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\TransaksiDo;
use App\Models\TutupHari;
use App\Models\JurnalKeuangan;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\TransaksiOperasional;
use App\Models\Perusahaan;
use Illuminate\Support\HtmlString;

class ListTransaksiDos extends ListRecords
{
    protected static string $resource = TransaksiDoResource::class;
    public ?string $activeTab = 'hari_ini';

    public function render(): \Illuminate\Contracts\View\View
    {
        return parent::render();
    }

    public function mount(): void
    {
        parent::mount();

        // Inject CSS untuk menyembunyikan baris "Halaman ini" di footer tabel
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::content.start',
            fn(): string => '<style>.fi-ta-summary-row:first-child { display: none !important; }</style>',
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    // Handle filter date changes
    public function updatedTableFilters(): void
    {
        $filter = $this->tableFilters['tanggal_range'] ?? null;
        if ($filter && isset($filter['dari_tanggal'], $filter['sampai_tanggal'])) {
            $this->dispatch('filter-transaksi', [
                'startDate' => $filter['dari_tanggal'],
                'endDate' => $filter['sampai_tanggal'],
            ]);
        }
    }

    // Handle tab changes
    public function updatedActiveTab(): void
    {
        if ($this->activeTab === 'semua') {

            $this->tableFilters = [
                'tanggal_range' => [
                    'dari_tanggal' => null,
                    'sampai_tanggal' => null,
                ],
            ];
        } else {

            $today = today()->toDateString();

            $this->tableFilters = [
                'tanggal_range' => [
                    'dari_tanggal' => $today,
                    'sampai_tanggal' => $today,
                ],
            ];
        }

        $this->resetTable();
        $this->resetPage();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\TransaksiDos\Widgets\TransaksiDoStatWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            //
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'hari_ini';
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->badge($this->getTabCount('hari_ini'))
                ->badgeColor('success'),

            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'tunai'))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'transfer'))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('cair_luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'cair di luar'))
                ->badgeColor('warning'),

            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('belum_dibayar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'belum dibayar'))
                ->badgeColor('danger'),
        ];
    }

    protected ?array $cachedTabCounts = null;

    protected function getTabCount(string $tab): int
    {
        if ($this->cachedTabCounts === null) {
            $tenantId = Filament::getTenant()?->id;
            if (!$tenantId) return 0;

            $today = today()->toDateString();

            $counts = DB::table('transaksi_do')
                ->where('perusahaan_id', $tenantId)
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*) as semua,
                    COUNT(CASE WHEN DATE(tanggal) = ? THEN 1 END) as hari_ini,
                    COUNT(CASE WHEN cara_bayar = 'tunai' AND DATE(tanggal) = ? THEN 1 END) as tunai,
                    COUNT(CASE WHEN cara_bayar = 'transfer' AND DATE(tanggal) = ? THEN 1 END) as transfer,
                    COUNT(CASE WHEN cara_bayar = 'cair di luar' AND DATE(tanggal) = ? THEN 1 END) as cair_luar,
                    COUNT(CASE WHEN cara_bayar = 'belum dibayar' AND DATE(tanggal) = ? THEN 1 END) as belum_dibayar
                ", [$today, $today, $today, $today, $today])
                ->first();

            $this->cachedTabCounts = (array) $counts;
        }

        return $this->cachedTabCounts[$tab] ?? 0;
    }

    protected static function getSummaryTableHtml(string|null $tanggal): HtmlString
    {
        if (!$tanggal) return new HtmlString('<div class="p-4 border border-dashed rounded-lg text-gray-400 text-center italic">Pilih tanggal untuk melihat ringkasan</div>');

        $perusahaanId = Filament::getTenant()->id;

        // Data Jurnal Keuangan yang Mempengaruhi Kas
        $masuk = JurnalKeuangan::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pemasukan')
            ->where('mempengaruhi_kas', true)
            ->sum('nominal');

        $keluar = JurnalKeuangan::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pengeluaran')
            ->where('mempengaruhi_kas', true)
            ->sum('nominal');

        // Data DO (Pembelian)
        $doCount = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
        $doTotal = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('sub_total');

        // Data Operasional
        $opCount = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
        $opTotal = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('nominal');

        // Saldo Awal: Ambil dari saldo_akhir_fisik TutupHari sebelumnya
        $lastClosing = TutupHari::where('perusahaan_id', '=', $perusahaanId, 'and')
            ->where('tanggal', '<', $tanggal, 'and')
            ->latest('tanggal')
            ->first();

        if ($lastClosing) {
            $saldoAwal = $lastClosing->saldo_akhir_fisik;
        } else {
            // Jika belum pernah tutup hari, ambil saldo perusahaan saat ini - net hari ini
            $perusahaan = Perusahaan::find($perusahaanId, ['*']);
            $saldoAwal = ($perusahaan?->saldo ?? 0) - ($masuk - $keluar);
        }

        $saldoSistem = $saldoAwal + $masuk - $keluar;

        return new HtmlString("
            <div class='overflow-hidden border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-lg bg-white dark:bg-gray-800' style='width: 100%;'>
                <table class='w-full text-base border-collapse tracking-wide' style='table-layout: fixed; width: 100%; border: 1px solid #d1d5db;'>
                    <thead>
                        <tr class='bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-600'>
                            <th class='w-2/3 pl-4 pr-8 py-6 font-bold text-gray-800 dark:text-gray-200 uppercase text-sm tracking-widest text-left border border-gray-300 dark:border-gray-600'>Keterangan Ringkasan</th>
                            <th class='w-1/3 px-8 py-6 font-bold text-gray-800 dark:text-gray-200 uppercase text-sm tracking-widest border border-gray-300 dark:border-gray-600' style='text-align: right;'>Jumlah / Nilai</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y-2 divide-gray-300 dark:divide-gray-600'>
                        <!-- Transaksi DO -->
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-primary-500 shadow-sm'></div>
                                    TRANSAKSI PEMBELIAN BUAH (DO)
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Banyak DO Hari Ini</td>
                            <td class='px-8 py-5 font-bold text-primary-600 text-lg border border-gray-300 dark:border-gray-600' style='text-align: right;'>{$doCount} DO</td>
                        </tr>
                        <tr class='hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Total Pembelian DO</td>
                            <td class='px-8 py-5 font-bold text-primary-600 font-mono text-xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($doTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Operasional -->
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-t-2 border-gray-300 dark:border-gray-600 border-b'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-warning-500 shadow-sm'></div>
                                    BIAYA OPERASIONAL
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-warning-50/50 dark:hover:bg-warning-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Jumlah Item Pengeluaran</td>
                            <td class='px-8 py-5 font-bold text-primary-600 text-lg border border-gray-300 dark:border-gray-600' style='text-align: right;'>{$opCount} Item</td>
                        </tr>
                        <tr class='hover:bg-warning-50/50 dark:hover:bg-warning-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Total Biaya Operasional</td>
                            <td class='px-8 py-5 font-bold text-primary-600 font-mono text-xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($opTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Arus Kas -->
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-t-2 border-gray-300 dark:border-gray-600 border-b'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-success-500 shadow-sm'></div>
                                    REKONSILIASI KAS (SISTEM)
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-success-50/50 dark:hover:bg-success-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-700 dark:text-gray-200 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Saldo Awal (Kas Tunai)</td>
                            <td class='px-8 py-5 font-bold font-mono text-primary-700 dark:text-primary-400 text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($saldoAwal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='hover:bg-success-50/50 dark:hover:bg-success-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-success-700 dark:text-success-400 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Total Pemasukan Tunai</td>
                            <td class='px-8 py-5 font-bold text-primary-700 dark:text-primary-400 font-mono text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($masuk, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='hover:bg-danger-50/50 dark:hover:bg-danger-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-danger-700 dark:text-danger-400 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Total Pengeluaran Tunai</td>
                            <td class='px-8 py-5 font-bold text-primary-700 dark:text-primary-400 font-mono text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($keluar, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-primary-50/80 dark:bg-primary-900/20 border-t-4 border-primary-500 shadow-inner'>
                            <td class='pl-4 pr-8 py-10 !font-black !text-primary-900 dark:!text-primary-100 uppercase text-3xl tracking-widest text-left border-r border-primary-200 dark:border-primary-700 border border-gray-300'>SALDO AKHIR SISTEM</td>
                            <td class='px-8 py-10 !font-black font-mono border border-gray-300 dark:border-gray-600' style='text-align: right; font-weight: 900 !important; font-size: 30px !important; color: #2563eb !important;'>Rp " . number_format($saldoSistem, 0, ',', '.') . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        ");
    }
}
