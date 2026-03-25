<?php

namespace App\Filament\Resources\TransaksiDos;

use App\Filament\Resources\TransaksiDos\Pages;
use App\Filament\Resources\TransaksiDos\Schemas\TransaksiDoForm;
use App\Filament\Resources\TransaksiDos\Tables\TransaksiDoTable;
use App\Filament\Resources\TransaksiDos\Widgets\TransaksiDoStatWidget;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransaksiDoResource extends Resource
{
    protected static ?string $model = \App\Models\TransaksiDo::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Transaksi DO';
    protected static ?string $modelLabel = 'Transaksi DO';
    protected static ?string $pluralModelLabel = 'Transaksi DO';
    protected static ?int $navigationSort = 1;


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['penjual', 'supir'])
            ->currentMonth()
            ->latest('tanggal');
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\JurnalKeuangans\Widgets\JurnalKeuanganDoStatsWidget::class,
        ];
    }

    public static function form(Schema $form): Schema
    {
        return TransaksiDoForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return TransaksiDoTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiDos::route('/'),
            'create' => Pages\CreateTransaksiDo::route('/create'),
            'edit' => Pages\EditTransaksiDo::route('/{record}/edit'),
        ];
    }
}
