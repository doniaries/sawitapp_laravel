<?php

namespace App\Filament\Resources\TutupHaris\Pages;

use App\Filament\Resources\TutupHariResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutupHaris extends ListRecords
{
    protected static string $resource = TutupHariResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tutup Hari Baru')
                ->modalWidth('7xl')
                ->createAnother(false)
                ->modalHeading('Proses Tutup Hari')
                ->using(function (array $data): \App\Models\TutupHari {
                    return \App\Models\TutupHari::performClosing(
                        $data,
                        \Filament\Facades\Filament::getTenant()->id
                    );
                }),
        ];
    }
}
