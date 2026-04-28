<?php

namespace App\Filament\Resources\Lansirs;

use App\Filament\Resources\Lansirs\Pages\CreateLansir;
use App\Filament\Resources\Lansirs\Pages\EditLansir;
use App\Filament\Resources\Lansirs\Pages\ListLansirs;
use App\Filament\Resources\Lansirs\Schemas\LansirForm;
use App\Filament\Resources\Lansirs\Tables\LansirsTable;
use App\Models\Lansir;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LansirResource extends Resource
{
    protected static ?string $model = Lansir::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Lansir Sawit';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'nama_penjual';

    public static function canViewAny(): bool
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        
        return $tenant && $tenant->type === 'khusus';
    }

    public static function form(Schema $schema): Schema
    {
        return LansirForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LansirsTable::configure($table);
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
            'index' => ListLansirs::route('/'),
            'create' => CreateLansir::route('/create'),
            'edit' => EditLansir::route('/{record}/edit'),
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
