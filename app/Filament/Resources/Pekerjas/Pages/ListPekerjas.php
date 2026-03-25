<?php

namespace App\Filament\Resources\Pekerjas\Pages;

use App\Filament\Resources\Pekerjas\PekerjaResource;
use App\Filament\Resources\Pekerjas\Widgets\PekerjaStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPekerjas extends ListRecords
{
    protected function getHeaderWidgets(): array
    {
        return [
            PekerjaStatsWidget::class,
        ];
    }
    protected static string $resource = PekerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
