<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTambahSaldo extends CreateRecord
{
    protected static string $resource = TambahSaldoResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        $record->loadMissing(['user', 'perusahaan']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
