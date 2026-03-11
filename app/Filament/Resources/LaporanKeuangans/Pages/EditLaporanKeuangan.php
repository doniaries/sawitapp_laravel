<?php

namespace App\Filament\Resources\LaporanKeuangans\Pages;

use App\Filament\Resources\LaporanKeuangans\LaporanKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanKeuangan extends EditRecord
{
    protected static string $resource = LaporanKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
