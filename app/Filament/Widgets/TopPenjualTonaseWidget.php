<?php

namespace App\Filament\Widgets;

use App\Models\Penjual;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

class TopPenjualTonaseWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Top 5 Penjual (Bulan Ini)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Penjual::query()
                    ->select('penjuals.*')
                    ->selectRaw('COALESCE(SUM(transaksi_do.tonase), 0) as total_tonase')
                    ->join('transaksi_do', 'penjuals.id', '=', 'transaksi_do.penjual_id')
                    ->whereNull('transaksi_do.deleted_at')
                    ->where('transaksi_do.perusahaan_id', Filament::getTenant()->id)
                    ->whereMonth('transaksi_do.tanggal', now()->month)
                    ->whereYear('transaksi_do.tanggal', now()->year)
                    ->groupBy('penjuals.id')
                    ->orderByDesc('total_tonase')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Penjual')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_tonase')
                    ->label('Total Tonase')
                    ->numeric(0, ',', '.')
                    ->suffix(' kg')
                    ->alignRight(),
            ])
            ->paginated(false);
    }
}
