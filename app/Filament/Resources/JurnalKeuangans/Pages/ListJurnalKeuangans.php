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
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', today()))
                ->badge($this->getTabCount('hari_ini')),

            'bulan_ini' => Tab::make('Bulan Ini')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
                ->badge($this->getTabCount('bulan_ini')),

            'tahun_ini' => Tab::make('Tahun Ini')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereYear('tanggal', now()->year))
                ->badge($this->getTabCount('tahun_ini')),

            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-clipboard-document-list')
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->badge($this->getTabCount('semua')),
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
            'hari_ini' => $query->whereDate('tanggal', today())->count(),
            'bulan_ini' => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'tahun_ini' => $query->whereYear('tanggal', now()->year)->count(),
            default => $query->count(),
        };
    }
}
