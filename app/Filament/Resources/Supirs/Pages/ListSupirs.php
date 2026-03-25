<?php

namespace App\Filament\Resources\Supirs\Pages;

use App\Filament\Resources\Supirs\SupirResource;
use App\Filament\Resources\Supirs\Widgets\SupirStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupirs extends ListRecords
{
    protected function getHeaderWidgets(): array
    {
        return [
            SupirStatsWidget::class,
        ];
    }
    protected static string $resource = SupirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
