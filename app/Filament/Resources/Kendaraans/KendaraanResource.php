<?php

namespace App\Filament\Resources\Kendaraans;

use App\Filament\Resources\Kendaraans\Pages;
use App\Filament\Resources\Kendaraans\Schemas\KendaraanForm;
use App\Filament\Resources\Kendaraans\Tables\KendaraanTable;
use App\Models\Kendaraan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KendaraanResource extends Resource
{
    protected static ?string $model = Kendaraan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Data Kendaraan';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return KendaraanForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return KendaraanTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKendaraans::route('/'),
            'create' => Pages\CreateKendaraan::route('/create'),
            'edit' => Pages\EditKendaraan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supir']);
    }
}
