<?php

namespace App\Filament\Resources\Operasionals\Tables;

use App\Models\Operasional;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class OperasionalTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('operasional')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray'
                    }),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn($record) => $record->kategoriLabel)
                    ->searchable(),

                TextColumn::make('user_id')
                    ->label('Karyawan')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->user?->name ?? '-'),

                TextColumn::make('penjual_id')
                    ->label('Penjual')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->penjual?->nama ?? '-'),

                TextColumn::make('supir_id')
                    ->label('Supir')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->supir?->nama ?? '-'),

                TextColumn::make('pekerja_id')
                    ->label('Pekerja')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->pekerja?->nama ?? '-'),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn($record) => 'Rp ' . number_format($record->nominal, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->currency('IDR')
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('operasional')
                    ->options(Operasional::JENIS_OPERASIONAL),
                TrashedFilter::make()
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
