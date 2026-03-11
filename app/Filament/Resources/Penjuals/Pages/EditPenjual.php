<?php

namespace App\Filament\Resources\PenjualResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PenjualResource;
use App\Filament\Resources\PenjualResource\RelationManagers\RiwayatHutangPinjamanRelationManager;

class EditPenjual extends EditRecord
{
    protected static string $resource = PenjualResource::class;

    public function getRelationManagers(): array
    {
        return [
            RiwayatHutangPinjamanRelationManager::class,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
