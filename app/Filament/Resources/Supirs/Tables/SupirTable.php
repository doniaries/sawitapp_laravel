<?php

namespace App\Filament\Resources\Supirs\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class SupirTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hutang')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('nama', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('has_hutang')
                    ->query(fn(Builder $query): Builder => $query->where('hutang', '>', 0))
                    ->label('Ada Hutang')
                    ->toggle()
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
