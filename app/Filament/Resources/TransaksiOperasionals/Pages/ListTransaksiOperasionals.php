<?php

namespace App\Filament\Resources\TransaksiOperasionals\Pages;

use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use App\Filament\Resources\TransaksiOperasionals\Widgets\TransaksiOperasionalStatsWidget;
use App\Models\TransaksiOperasional;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransaksiOperasionals extends ListRecords
{
    protected static string $resource = TransaksiOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
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
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', today()))
                ->badge(TransaksiOperasional::whereDate('tanggal', today())->count())
                ->badgeColor('success'),

            'pemasukan' => Tab::make('Pemasukan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pemasukan')->whereDate('tanggal', today())
                )
                ->icon('heroicon-o-arrow-trending-up')
                ->badge(TransaksiOperasional::where('operasional', 'pemasukan')->whereDate('tanggal', today())->count())
                ->badgeColor('success'),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pengeluaran')->whereDate('tanggal', today())
                )
                ->icon('heroicon-o-arrow-trending-down')
                ->badge(TransaksiOperasional::where('operasional', 'pengeluaran')->whereDate('tanggal', today())->count())
                ->badgeColor('danger'),

            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(TransaksiOperasional::count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransaksiOperasionalStatsWidget::class,
        ];
    }
}
