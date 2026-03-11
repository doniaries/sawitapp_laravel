<?php

namespace App\Filament\Resources\Penjuals;

use App\Filament\Resources\Penjuals\Pages;
use App\Filament\Resources\Penjuals\RelationManagers;
use App\Filament\Resources\Penjuals\Schemas\PenjualForm;
use App\Filament\Resources\Penjuals\Tables\PenjualTable;
use App\Models\Penjual;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenjualResource extends Resource
{
    protected static ?string $model = Penjual::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 4;

    public static function getRelations(): array
    {
        return [
            RelationManagers\RiwayatHutangPinjamanRelationManager::class,
            RelationManagers\RiwayatPembayaranHutangRelationManager::class,
        ];
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
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
}
