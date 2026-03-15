<?php

namespace App\Filament\Resources\TransaksiOperasionals\Tables;

use App\Models\TransaksiOperasional;
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

class TransaksiOperasionalTable
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

                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('supir.nama')
                    ->label('Supir')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('pekerja.nama')
                    ->label('Pekerja')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->currency('IDR')
                    ->alignRight()
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->currency('IDR')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('operasional')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),
                TrashedFilter::make()
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
