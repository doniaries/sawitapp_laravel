<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use App\Filament\Resources\TambahSaldos\Widgets\TambahSaldoStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTambahSaldos extends ListRecords
{
    protected static string $resource = TambahSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Baru'),
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
                ->badge(\App\Models\TambahSaldo::whereDate('tanggal', today())->count()),

            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(\App\Models\TambahSaldo::count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TambahSaldoStats::class,
        ];
    }
}
