<?php

namespace App\Filament\Resources\Operasionals\Pages;

use App\Filament\Resources\Operasionals\OperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOperasional extends CreateRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
