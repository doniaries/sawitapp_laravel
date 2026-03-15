<?php

namespace App\Filament\Resources\PengajuanDanas\Pages;

use App\Filament\Resources\PengajuanDanas\PengajuanDanaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanDanas extends ListRecords
{
    protected static string $resource = PengajuanDanaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PengajuanDanas\Widgets\PengajuanDanaStats::class,
        ];
    }
}
