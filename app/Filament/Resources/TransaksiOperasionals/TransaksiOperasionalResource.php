<?php

namespace App\Filament\Resources\TransaksiOperasionals;

use App\Filament\Resources\TransaksiOperasionals\Pages;
use App\Filament\Resources\TransaksiOperasionals\Schemas;
use App\Filament\Resources\TransaksiOperasionals\Tables;
use App\Models\TransaksiOperasional;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiOperasionalResource extends Resource
{
    protected static ?string $model = TransaksiOperasional::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transaksi Operasional';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Transaksi Operasional';
    protected static ?string $pluralModelLabel = 'Transaksi Operasional';



    public static function form(Schema $schema): Schema
    {
        return Schemas\TransaksiOperasionalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\TransaksiOperasionalTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiOperasionals::route('/'),
            'create' => Pages\CreateTransaksiOperasional::route('/create'),
            'edit' => Pages\EditTransaksiOperasional::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['pihak', 'user']);
    }
}
