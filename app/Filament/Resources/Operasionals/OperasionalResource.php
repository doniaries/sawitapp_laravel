<?php

namespace App\Filament\Resources\Operasionals;

use App\Filament\Resources\Operasionals\Pages;
use App\Filament\Resources\Operasionals\Schemas\OperasionalForm;
use App\Filament\Resources\Operasionals\Tables\OperasionalTable;
use App\Models\Operasional;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OperasionalResource extends Resource
{
    protected static ?string $model = Operasional::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Op';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->whereDate('created_at', today())
            ->count();
    }

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return OperasionalForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return OperasionalTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperasionals::route('/'),
            'create' => Pages\CreateOperasional::route('/create'),
            'edit' => Pages\EditOperasional::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['penjual', 'supir', 'pekerja', 'user']);
    }
}
