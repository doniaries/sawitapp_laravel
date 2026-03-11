<?php
// Path: App\Filament\Resources\PenjualResource\RelationManagers\RiwayatPembayaranHutangRelationManager.php

namespace App\Filament\Resources\PenjualResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatPembayaranHutangRelationManager extends RelationManager
{
    // Definisi relationship yang digunakan
    protected static string $relationship = 'operasional';

    // Kustomisasi label
    protected static ?string $title = 'Riwayat Pembayaran Hutang';
    protected static ?string $modelLabel = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran Hutang';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //... form fields jika diperlukan
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal')
            ->columns([
                // Kolom tanggal transaksi
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->label('Tanggal')
                    ->sortable(),

                // Kolom kategori operasional
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label() ?? '-')
                    ->colors([
                        'danger' => 'pinjaman',
                        'success' => 'bayar_hutang'
                    ]),

                // Kolom nominal
                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR')
                    ->label('Nominal')
                    ->alignEnd()
                    ->sortable(),

                // Kolom cara pembayaran
                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->label('Cara Bayar')
                    ->badge(),

                // Kolom keterangan
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->wrap(),

                // Kolom bukti pembayaran
                Tables\Columns\ImageColumn::make('file_bukti')
                    ->label('Bukti')
                    ->disk('public')
                    ->circular(),
            ])
            // Filter untuk hanya tampilkan kategori terkait hutang
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->whereIn('kategori', ['bayar_hutang', 'pinjaman'])
                    ->where('tipe_nama', 'penjual')
            )
            ->defaultSort('tanggal', 'desc')
            ->filters([
                // ... tambahkan filter jika diperlukan
            ])
            ->headerActions([
                // ... tambahkan action header jika diperlukan
            ])
            ->actions([
                // ... tambahkan action pada tiap record jika diperlukan
            ])
            ->bulkActions([
                // ... tambahkan bulk action jika diperlukan
            ]);
    }
}
