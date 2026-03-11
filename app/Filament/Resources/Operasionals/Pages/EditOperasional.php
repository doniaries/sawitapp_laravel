<?php

namespace App\Filament\Resources\Operasionals\Pages;

use App\Filament\Resources\Operasionals\OperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperasional extends EditRecord
{
    protected static string $resource = OperasionalResource::class;

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
