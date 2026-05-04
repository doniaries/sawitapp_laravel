<?php

namespace App\Filament\Resources\TambahSaldos;

use App\Filament\Resources\TambahSaldos\Pages;
use App\Filament\Resources\TambahSaldos\Schemas\TambahSaldoForm;
use App\Filament\Resources\TambahSaldos\Tables\TambahSaldosTable;
use App\Models\TambahSaldo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TambahSaldoResource extends Resource
{
    protected static ?string $model = TambahSaldo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Tambah Saldo';

    protected static ?string $pluralLabel = 'Tambah Saldo';

    protected static ?string $modelLabel = 'Tambah Saldo';

    protected static ?string $slug = 'tambah-saldo';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TambahSaldoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TambahSaldosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            // PengajuanDanaStats::class, // Removed as per instruction's implied change
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTambahSaldos::route('/'),
            'create' => Pages\CreateTambahSaldo::route('/create'),
            'edit' => Pages\EditTambahSaldo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['perusahaan', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
