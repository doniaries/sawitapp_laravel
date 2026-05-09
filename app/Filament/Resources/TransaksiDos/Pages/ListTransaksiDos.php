<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

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

    public function getHeaderWidgets(): array
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
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', today()))
                ->badgeColor('success'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'tunai')->whereDate('tanggal', today()))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'transfer')->whereDate('tanggal', today()))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('cair_luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'cair di luar')->whereDate('tanggal', today()))
                ->badgeColor('warning'),

            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('belum_dibayar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'belum dibayar')->whereDate('tanggal', today()))
                ->badgeColor('danger'),
                
            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->badgeColor('primary'),
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
}
