<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\TransaksiDo;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransaksiDos extends ListRecords
{
    protected static string $resource = TransaksiDoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // Handle filter date changes
    public function updatedTableFilters(): void
    {
        $filter = $this->tableFilters['tanggal'] ?? null;
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
        $this->dispatch('tab-changed', [
            'tab' => $this->activeTab
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\JurnalKeuangans\Widgets\JurnalKeuanganDoStatsWidget::class,
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
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today(), 'and'))
                ->badge($this->getTabCount('hari_ini'))
                ->badgeColor('success'),

            'kemarin' => Tab::make('Kemarin')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', now()->subDay()->toDateString(), 'and'))
                ->badge($this->getTabCount('kemarin'))
                ->badgeColor('warning'),

            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'tunai', 'and'))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'transfer', 'and'))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('cair_luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'cair di luar', 'and'))
                ->badgeColor('warning'),

            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('belum_dibayar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'belum dibayar', 'and'))
                ->badgeColor('danger'),
        ];
    }

    protected function getTabCount(string $tab): int
    {
        $query = TransaksiDo::query();
        $filter = $this->tableFilters['tanggal'] ?? null;

        if (in_array($tab, ['semua', 'tunai', 'transfer', 'cair_luar', 'belum_dibayar'])) {
            if ($filter && !empty($filter['dari_tanggal']) && !empty($filter['sampai_tanggal'])) {
                $query->whereBetween('tanggal', [$filter['dari_tanggal'], $filter['sampai_tanggal']], 'and', false);
            } else {
                $query->currentMonth();
            }
        }

        return match ($tab) {
            'hari_ini' => $query->whereDate('tanggal', '=', today(), 'and')->count('*'),
            'kemarin' => $query->whereDate('tanggal', '=', now()->subDay()->toDateString(), 'and')->count('*'),
            'tunai' => $query->where('cara_bayar', '=', 'tunai', 'and')->count('*'),
            'transfer' => $query->where('cara_bayar', '=', 'transfer', 'and')->count('*'),
            'cair_luar' => $query->where('cara_bayar', '=', 'cair di luar', 'and')->count('*'),
            'belum_dibayar' => $query->where('cara_bayar', '=', 'belum dibayar', 'and')->count('*'),
            default => $query->count('*'),
        };
    }
}
