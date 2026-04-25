<?php

namespace App\Filament\Resources\Supirs;

use App\Filament\Resources\Supirs\Pages;
use App\Filament\Resources\Supirs\Schemas\SupirForm;
use App\Filament\Resources\Supirs\Tables\SupirTable;
use App\Models\Supir;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupirResource extends Resource
{
    protected static ?string $model = \App\Models\Supir::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return SupirForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SupirTable::configure($table);
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
            'index' => Pages\ListSupirs::route('/'),
            'create' => Pages\CreateSupir::route('/create'),
            'edit' => Pages\EditSupir::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['transaksiDo'])
            ->withSum('riwayatPembayaran as riwayat_pembayaran_sum_nominal', 'nominal')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
