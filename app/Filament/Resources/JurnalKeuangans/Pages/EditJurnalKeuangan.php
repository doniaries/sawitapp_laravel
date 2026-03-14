<?php

namespace App\Filament\Resources\JurnalKeuangans\Pages;

use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJurnalKeuangan extends EditRecord
{
    protected static string $resource = JurnalKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
