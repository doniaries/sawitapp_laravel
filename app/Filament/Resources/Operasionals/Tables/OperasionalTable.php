<?php

namespace App\Filament\Resources\Operasionals\Tables;

use App\Models\Operasional;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class OperasionalTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('operasional')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn($record) => $record->kategoriLabel)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('Karyawan')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->user?->name ?? '-'),

                Tables\Columns\TextColumn::make('penjual_id')
                    ->label('Penjual')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->penjual?->nama ?? '-'),

                Tables\Columns\TextColumn::make('supir_id')
                    ->label('Supir')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->supir?->nama ?? '-'),

                Tables\Columns\TextColumn::make('pekerja_id')
                    ->label('Pekerja')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->pekerja?->nama ?? '-'),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn($record) => 'Rp ' . number_format($record->nominal, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR')
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('operasional')
                    ->options(Operasional::JENIS_OPERASIONAL),
                Tables\Filters\TrashedFilter::make()
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
