<?php

namespace App\Filament\Resources\TutupHaris\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Colors\Color;

class TutupHariTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                TextColumn::make('total_do_tonase')
                    ->label('Total DO (Kg)')
                    ->numeric(0, ',', '.')
                    ->suffix(' Kg')
                    ->toggleable(),

                TextColumn::make('total_do_rupiah')
                    ->label('Total DO (Rp)')
                    ->currency('IDR')
                    ->toggleable(),

                TextColumn::make('saldo_akhir_sistem')
                    ->label('Saldo Sistem')
                    ->currency('IDR')
                    ->color(Color::Blue)
                    ->weight('bold'),

                TextColumn::make('saldo_akhir_fisik')
                    ->label('Saldo Fisik')
                    ->currency('IDR')
                    ->color(Color::Green)
                    ->weight('bold'),

                TextColumn::make('selisih')
                    ->label('Selisih')
                    ->currency('IDR')
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success'))
                    ->weight('bold'),

                TextColumn::make('user.nama')
                    ->label('Oleh')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
