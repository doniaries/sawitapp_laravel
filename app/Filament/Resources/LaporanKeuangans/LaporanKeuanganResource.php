<?php

namespace App\Filament\Resources\LaporanKeuangans;

use App\Filament\Resources\LaporanKeuangans\Pages;
use App\Filament\Resources\LaporanKeuangans\Schemas\LaporanKeuanganForm;
use App\Filament\Resources\LaporanKeuangans\Tables\LaporanKeuanganTable;
use App\Filament\Resources\LaporanKeuangans\Widgets\LaporanKeuanganDoStatsWidget;
use App\Models\LaporanKeuangan;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Schemas\Schema;

class LaporanKeuanganResource extends Resource
{
    protected static ?string $model = \App\Models\LaporanKeuangan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return LaporanKeuanganForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return LaporanKeuanganTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanKeuangans::route('/'),
            'view' => Pages\ViewLaporanKeuangan::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            LaporanKeuanganDoStatsWidget::class,
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
