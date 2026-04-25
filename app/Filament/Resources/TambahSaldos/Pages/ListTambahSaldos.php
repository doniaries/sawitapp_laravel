<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use App\Filament\Resources\TambahSaldos\Widgets\TambahSaldoStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTambahSaldos extends ListRecords
{
    protected static string $resource = TambahSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Baru'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TambahSaldoStats::class,
        ];
    }
}
