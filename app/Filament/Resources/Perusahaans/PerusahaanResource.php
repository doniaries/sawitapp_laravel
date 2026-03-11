<?php

namespace App\Filament\Resources\Perusahaans;

use App\Filament\Resources\Perusahaans\Pages;
use App\Filament\Resources\Perusahaans\RelationManagers;
use App\Filament\Resources\Perusahaans\Schemas\PerusahaanForm;
use App\Filament\Resources\Perusahaans\Tables\PerusahaanTable;
use App\Models\Perusahaan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerusahaanResource extends Resource
{
    protected static ?string $model = \App\Models\Perusahaan::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return PerusahaanForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PerusahaanTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RiwayatSaldoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerusahaans::route('/'),
            'create' => Pages\CreatePerusahaan::route('/create'),
            'edit' => Pages\EditPerusahaan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
