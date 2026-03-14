<?php

namespace App\Filament\Resources\JurnalKeuangans;

use App\Models\JurnalKeuangan;
use App\Filament\Resources\JurnalKeuangans\Pages;
use App\Filament\Resources\JurnalKeuangans\Schemas;
use App\Filament\Resources\JurnalKeuangans\Tables;
use App\Filament\Resources\JurnalKeuangans\Widgets;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JurnalKeuanganResource extends Resource
{
    protected static ?string $model = JurnalKeuangan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Jurnal Keuangan';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return 'Jurnal Keuangan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jurnal Keuangan';
    }

    public static function form(Schema $schema): Schema
    {
        return Schemas\JurnalKeuanganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\JurnalKeuanganTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalKeuangans::route('/'),
            'view' => Pages\ViewJurnalKeuangan::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\JurnalKeuanganDoStatsWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supir', 'pekerja', 'penjual', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
