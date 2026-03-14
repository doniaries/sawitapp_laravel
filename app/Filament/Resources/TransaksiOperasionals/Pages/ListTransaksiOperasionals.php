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
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn() => TransaksiOperasional::count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('tanggal', '>=', now()->subDays(30))
                ),

            'pemasukan' => Tab::make('Pemasukan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pemasukan')
                )
                ->icon('heroicon-o-arrow-trending-up')
                ->badge(fn() => TransaksiOperasional::where('operasional', 'pemasukan')->count())
                ->badgeColor('success'),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pengeluaran')
                )
                ->icon('heroicon-o-arrow-trending-down')
                ->badge(fn() => TransaksiOperasional::where('operasional', 'pengeluaran')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransaksiOperasionalStatsWidget::class,
        ];
    }
}
