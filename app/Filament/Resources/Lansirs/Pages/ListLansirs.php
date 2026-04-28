<?php

namespace App\Filament\Resources\Lansirs\Pages;

use App\Filament\Resources\Lansirs\LansirResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLansirs extends ListRecords
{
    protected static string $resource = LansirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
