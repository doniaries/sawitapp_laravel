<?php

namespace App\Filament\Resources\Lansirs\Pages;

use App\Filament\Resources\Lansirs\LansirResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLansirs extends ListRecords
{
    protected static string $resource = LansirResource::class;

    public function getDefaultActiveTab(): string | int | null
    {
        return 'hari_ini';
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->whereDate('tanggal', today()))
                ->badge(\App\Models\Lansir::whereDate('tanggal', today())->count())
                ->badgeColor('success'),

            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(\App\Models\Lansir::count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Lansir Baru'),
        ];
    }
}
