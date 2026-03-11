<?php

namespace App\Filament\Resources\PenjualResource\Pages;

use App\Filament\Resources\PenjualResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePenjual extends CreateRecord
{
    protected static string $resource = PenjualResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Notifikasi sukses
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->duration(3000) // Set durasi 3 detik
            ->persistent(false) // Notifikasi akan otomatis hilang
            ->title('Penjual berhasil ditambahkan')
            ->body('Data penjual baru telah berhasil disimpan');
    }
}
