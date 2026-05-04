<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;

use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\Auth;

class CreateTambahSaldo extends CreateRecord
{
    protected static string $resource = TambahSaldoResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

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
