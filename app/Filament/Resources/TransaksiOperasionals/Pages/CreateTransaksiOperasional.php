<?php

namespace App\Filament\Resources\TransaksiOperasionals\Pages;

use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
 
class CreateTransaksiOperasional extends CreateRecord
{
    protected static string $resource = TransaksiOperasionalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
