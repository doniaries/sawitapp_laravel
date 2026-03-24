<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use App\Filament\Resources\TambahSaldos\Widgets\TambahSaldoStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTambahSaldos extends ListRecords
{
    protected static string $resource = TambahSaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn() => \App\Models\TambahSaldo::count()),

            'pending' => Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'pending'))
                ->badge(fn() => \App\Models\TambahSaldo::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'disetujui' => Tab::make('Disetujui')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'disetujui'))
                ->badge(fn() => \App\Models\TambahSaldo::where('status', 'disetujui')->count())
                ->badgeColor('success'),

            'ditolak' => Tab::make('Ditolak')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'ditolak'))
                ->badge(fn() => \App\Models\TambahSaldo::where('status', 'ditolak')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TambahSaldoStats::class,
        ];
    }
}
