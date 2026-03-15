<?php

namespace App\Filament\Resources\PengajuanDanas\Pages;

use App\Filament\Resources\PengajuanDanas\PengajuanDanaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPengajuanDanas extends ListRecords
{
    protected static string $resource = PengajuanDanaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn() => \App\Models\PengajuanDana::count()),
            
            'pending' => Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(fn() => \App\Models\PengajuanDana::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'disetujui' => Tab::make('Disetujui')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'disetujui'))
                ->badge(fn() => \App\Models\PengajuanDana::where('status', 'disetujui')->count())
                ->badgeColor('success'),
                
            'ditolak' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'ditolak'))
                ->badge(fn() => \App\Models\PengajuanDana::where('status', 'ditolak')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PengajuanDanas\Widgets\PengajuanDanaStats::class,
        ];
    }
}
