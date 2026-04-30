<?php

namespace App\Filament\Resources\JurnalKeuangans\Pages;

use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use App\Filament\Resources\JurnalKeuangans\Widgets\JurnalKeuanganDoStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\JurnalKeuangan;

class ListJurnalKeuangans extends ListRecords
{
    protected static string $resource = JurnalKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JurnalKeuanganDoStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today(), 'and'))
                ->badge($this->getTabCount('hari_ini')),

            'kemarin' => Tab::make('Kemarin')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today()->subDay(), 'and'))
                ->badge($this->getTabCount('kemarin')),

            'pemasukan' => Tab::make('Pemasukan')
                ->icon('heroicon-o-arrow-down-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', '=', 'Pemasukan', 'and'))
                ->badge($this->getTabCount('pemasukan')),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->icon('heroicon-o-arrow-up-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', '=', 'Pengeluaran', 'and'))
                ->badge($this->getTabCount('pengeluaran')),

            'bulan_ini' => Tab::make('Bulan Ini')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('tanggal', '=', now()->month, 'and')->whereYear('tanggal', '=', now()->year, 'and'))
                ->badge($this->getTabCount('bulan_ini')),
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', tab: $this->activeTab)->to(JurnalKeuanganDoStatsWidget::class);
    }

    protected function getTabCount(string $tab): int
    {
        $query = JurnalKeuangan::query();

        return match ($tab) {
            'hari_ini' => $query->whereDate('tanggal', '=', today(), 'and')->count('*'),
            'kemarin' => $query->whereDate('tanggal', '=', today()->subDay(), 'and')->count('*'),
            'pemasukan' => $query->where('jenis_transaksi', '=', 'Pemasukan', 'and')->count('*'),
            'pengeluaran' => $query->where('jenis_transaksi', '=', 'Pengeluaran', 'and')->count('*'),
            'bulan_ini' => $query->whereMonth('tanggal', '=', now()->month, 'and')->whereYear('tanggal', '=', now()->year, 'and')->count('*'),
            default => $query->count('*'),
        };
    }
}
