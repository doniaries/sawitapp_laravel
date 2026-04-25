<?php

namespace App\Filament\Resources\Penjuals\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Penjuals\PenjualResource;
use App\Filament\Resources\Penjuals\RelationManagers\RiwayatHutangPinjamanRelationManager;
use App\Filament\Resources\Penjuals\RelationManagers\RiwayatPembayaranHutangRelationManager;

class ViewPenjual extends ViewRecord
{
    protected static string $resource = PenjualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Data'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RiwayatHutangPinjamanRelationManager::class,
            RiwayatPembayaranHutangRelationManager::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true; // Tampilkan info penjual + tab relasi dalam satu halaman terpadu
    }
}
