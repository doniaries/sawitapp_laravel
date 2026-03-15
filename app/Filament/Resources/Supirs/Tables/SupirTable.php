<?php

namespace App\Filament\Resources\Supirs\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SupirTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('telepon')
                    ->searchable(),

                TextColumn::make('hutang')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('nama', 'asc')
            ->filters([
                TrashedFilter::make(),
                Filter::make('has_hutang')
                    ->query(fn(Builder $query): Builder => $query->where('hutang', '>', 0))
                    ->label('Ada Hutang')
                    ->toggle()
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Supir'),
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
