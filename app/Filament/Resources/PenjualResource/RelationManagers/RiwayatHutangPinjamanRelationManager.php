<?php

namespace App\Filament\Resources\PenjualResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class RiwayatHutangPinjamanRelationManager extends RelationManager
{
    protected static string $relationship = 'laporanKeuangan';

    protected static ?string $title = 'Riwayat Hutang & Pembayaran';

    protected static ?string $modelLabel = 'Riwayat';

    protected static ?string $pluralModelLabel = 'Riwayat';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_referensi')
                    ->label('No. Referensi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->badge(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc');
    }
}