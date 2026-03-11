<?php

namespace App\Filament\Resources\SupirResource\Pages;

use App\Filament\Resources\SupirResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupir extends EditRecord
{
    protected static string $resource = SupirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
