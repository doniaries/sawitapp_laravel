<?php

namespace App\Filament\Resources\Lansirs\Pages;

use App\Filament\Resources\Lansirs\LansirResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLansir extends EditRecord
{
    protected static string $resource = LansirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
