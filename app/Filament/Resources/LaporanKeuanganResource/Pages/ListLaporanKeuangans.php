<?php

namespace App\Filament\Resources\LaporanKeuanganResource\Pages;

use Filament\Actions;
// use App\Filament\Resources\LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget;
// use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use Livewire\Attributes\On;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\LaporanKeuanganResource;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ListLaporanKeuangans extends ListRecords
{
    protected static string $resource = LaporanKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget::class,
        ];
    }

    // Handle filter date dan update widget
    public function updatedTableFilters(): void
    {
        $filter = $this->getTableFilters()['date_range'] ?? null;
        if ($filter) {
            $this->dispatch('filter-laporan', [
                'startDate' => $filter->getState()['dari_tanggal'],
                'endDate' => $filter->getState()['sampai_tanggal'],
            ]);
        }
    }
    // Handle perubahan tab
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', [
            'tab' => $this->activeTab
        ]);
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', now()))
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

    protected function getTabCount(string $tab): int
    {
        $query = $this->getModel()::query();

        return match ($tab) {
            'hari_ini' => $query->whereDate('tanggal', now())->count(),
            'bulan_ini' => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'tahun_ini' => $query->whereYear('tanggal', now()->year)->count(),
            default => $query->count(),
        };
    }
}
