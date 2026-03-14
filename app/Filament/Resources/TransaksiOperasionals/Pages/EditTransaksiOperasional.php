<?php

namespace App\Filament\Resources\TransaksiOperasionals\Pages;

use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiOperasional extends EditRecord
{
    protected static string $resource = TransaksiOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
