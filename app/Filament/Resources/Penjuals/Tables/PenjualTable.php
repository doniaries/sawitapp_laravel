<?php

namespace App\Filament\Resources\Penjuals\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction;

class PenjualTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hutang')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignment('right')
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama', 'asc')
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('30s')
            ->persistSortInSession()
            ->bulkActions([
                BulkAction::make()
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Support\Collection $records) {
                        $records->each(function ($record) {
                            if ($record->hutang > 0) {
                                throw new \Exception("Penjual {$record->nama} masih memiliki hutang. Tidak dapat dihapus.");
                            }
                        });
                        $records->each->delete();
                    }),
            ]);
    }
}
