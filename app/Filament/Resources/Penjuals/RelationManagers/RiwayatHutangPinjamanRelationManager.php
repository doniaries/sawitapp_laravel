<?php

namespace App\Filament\Resources\Penjuals\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class RiwayatHutangPinjamanRelationManager extends RelationManager
{
    // Gunakan relasi morphMany mutasiHutang yang ada di model Penjual
    protected static string $relationship = 'mutasiHutang';

    protected static ?string $title = '📋 Riwayat Mutasi Hutang';
    protected static ?string $modelLabel = 'Mutasi';
    protected static ?string $pluralModelLabel = 'Riwayat Hutang';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'tambah' => 'Hutang Bertambah',
                        'kurang' => 'Hutang Berkurang',
                        default => ucfirst($state),
                    })
                    ->color(fn($state) => match ($state) {
                        'tambah' => 'danger',
                        'kurang' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo_akhir')
                    ->label('Sisa Hutang')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignEnd()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(60),
            ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada riwayat hutang')
            ->emptyStateDescription('Riwayat mutasi hutang penjual akan tampil di sini.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
