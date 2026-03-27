<?php

namespace App\Filament\Resources\Perusahaans\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Filament\Resources\Perusahaans\Widgets\PerusahaanStatsWidget;

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
