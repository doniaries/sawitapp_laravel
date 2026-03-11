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

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 4;

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
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
            //
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
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
