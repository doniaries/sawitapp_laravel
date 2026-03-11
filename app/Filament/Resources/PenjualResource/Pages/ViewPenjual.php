<?php

namespace App\Filament\Resources\PenjualResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PenjualResource;
use App\Filament\Resources\PenjualResource\RelationManagers\RiwayatHutangPinjamanRelationManager;

class ViewPenjual extends ViewRecord
{
    protected static string $resource = PenjualResource::class;

    public function getRelationManagers(): array
    {
        return [
            RiwayatHutangPinjamanRelationManager::class,
        ];
    }

    // protected function hasCombinedRelationManagerTabsWithContent(): bool
    // {
    //     return true;
    // }
}
