<?php

namespace App\Filament\Resources\PerusahaanResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PerusahaanResource;
use App\Filament\Resources\PerusahaanResource\Widgets\PerusahaanStatsWidget;

class ListPerusahaans extends ListRecords
{
    protected static string $resource = PerusahaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array  // Perubahan di sini
    {
        return [
            PerusahaanStatsWidget::class,
        ];
    }
}
