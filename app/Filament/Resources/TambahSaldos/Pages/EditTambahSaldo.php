<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTambahSaldo extends EditRecord
{
    protected static string $resource = TambahSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
