<?php

namespace App\Filament\Resources\TransaksiDos\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Colors\Color;

class TransaksiDoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color(Color::Blue),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cara_bayar')
                    ->label('Cara Bayar')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tunai' => 'success',
                        'transfer' => 'info',
                        'cair di luar' => 'warning',
                        'belum dibayar' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supir.nama')
                    ->label('Supir')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->numeric()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->suffix(' Kg')
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(Color::Amber)
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->currency('IDR', true)
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
