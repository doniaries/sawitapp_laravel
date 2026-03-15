<?php

namespace App\Filament\Resources\Penjuals\Pages;

use App\Filament\Resources\Penjuals\PenjualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Penjuals\Widgets\PenjualStatsOverview;
use App\Filament\Resources\Penjuals\RelationManagers\RiwayatHutangPinjamanRelationManager;


class ListPenjuals extends ListRecords
{
    protected static string $resource = PenjualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PenjualStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // RelationManagers\RiwayatHutangPinjamanRelationManager::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Daftar Penjual';
    }
}
