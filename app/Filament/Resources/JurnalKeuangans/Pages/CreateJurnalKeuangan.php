<?php

namespace App\Filament\Resources\JurnalKeuangans\Pages;

use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJurnalKeuangan extends CreateRecord
{
    protected static string $resource = JurnalKeuanganResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Jurnal Keuangan berhasil dibuat';
    }
}
