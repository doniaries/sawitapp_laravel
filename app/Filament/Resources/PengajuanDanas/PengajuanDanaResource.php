<?php

namespace App\Filament\Resources\PengajuanDanas;

use App\Filament\Resources\PengajuanDanas\Pages\CreatePengajuanDana;
use App\Filament\Resources\PengajuanDanas\Pages\EditPengajuanDana;
use App\Filament\Resources\PengajuanDanas\Pages\ListPengajuanDanas;
use App\Filament\Resources\PengajuanDanas\Schemas\PengajuanDanaForm;
use App\Filament\Resources\PengajuanDanas\Tables\PengajuanDanasTable;
use App\Models\PengajuanDana;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanDanaResource extends Resource
{
    protected static ?string $model = PengajuanDana::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'keperluan';

    public static function form(Schema $schema): Schema
    {
        return PengajuanDanaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengajuanDanasTable::configure($table);
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
            'index' => ListPengajuanDanas::route('/'),
            'create' => CreatePengajuanDana::route('/create'),
            'edit' => EditPengajuanDana::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
