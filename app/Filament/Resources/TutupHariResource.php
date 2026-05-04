<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutupHaris\Pages;
use App\Filament\Resources\TutupHaris\Schemas\TutupHariForm;
use App\Filament\Resources\TutupHaris\Tables\TutupHariTable;
use App\Models\TutupHari;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TutupHariResource extends Resource
{
    protected static ?string $model = TutupHari::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Tutup Hari';

    protected static ?string $modelLabel = 'Tutup Hari';

    protected static ?string $pluralModelLabel = 'Tutup Hari';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return TutupHariForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return TutupHariTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutupHaris::route('/'),
        ];
    }
}
