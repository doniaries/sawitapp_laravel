<?php

namespace App\Filament\Resources\Pekerjas;

use App\Filament\Resources\Pekerjas\Pages;
use App\Filament\Resources\Pekerjas\Schemas\PekerjaForm;
use App\Filament\Resources\Pekerjas\Tables\PekerjaTable;
use App\Models\Pekerja;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PekerjaResource extends Resource
{
    protected static ?string $model = \App\Models\Pekerja::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pekerja';
    protected static ?string $modelLabel = 'Pekerja';
    protected static ?string $pluralModelLabel = 'Pekerja';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'nama';

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Telepon' => $record->telepon ?? '-',
            'Alamat' => $record->alamat ?? '-',
        ];
    }

    public static function form(Schema $form): Schema
    {
        return PekerjaForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PekerjaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\Shared\RiwayatHutangPinjamanRelationManager::class,
            \App\Filament\RelationManagers\Shared\RiwayatPembayaranHutangRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPekerjas::route('/'),
            'create' => Pages\CreatePekerja::route('/create'),
            'edit' => Pages\EditPekerja::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSisaHutang()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $cacheKey = "pekerja_count_tenant_{$tenantId}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            return static::getModel()::count();
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
