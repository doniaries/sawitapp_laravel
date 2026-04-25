<?php

namespace App\Filament\Resources\Penjuals;

use App\Filament\Resources\Penjuals\Pages;

use App\Filament\Resources\Penjuals\Schemas\PenjualForm;
use App\Filament\Resources\Penjuals\Tables\PenjualTable;
use App\Models\Penjual;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Filament\Schemas\Schema;

class PenjualResource extends Resource
{
    protected static ?string $model = Penjual::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Penjual';
    protected static ?string $modelLabel = 'Penjual';
    protected static ?string $pluralModelLabel = 'Daftar Penjual';
    protected static ?string $recordTitleAttribute = 'nama';
    protected static int $globalSearchResultsLimit = 10;

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Telepon' => $record->telepon ?? '-',
            'Alamat' => $record->alamat ?? '-',
        ];
    }


    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\Shared\RiwayatHutangPinjamanRelationManager::class,
            \App\Filament\RelationManagers\Shared\RiwayatPembayaranHutangRelationManager::class,
        ];
    }

    public static function form(Schema $form): Schema
    {
        return PenjualForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PenjualTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjuals::route('/'),
            'create' => Pages\CreatePenjual::route('/create'),
            'view' => Pages\ViewPenjual::route('/{record}'),
            'edit' => Pages\EditPenjual::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('riwayatPembayaran as total_pembayaran_sum', 'nominal');
    }
}
