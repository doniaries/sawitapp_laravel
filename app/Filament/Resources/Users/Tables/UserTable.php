<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('perusahaans.name')
                    ->label('Perusahaan')
                    ->badge()
                    ->placeholder('Semua Perusahaan')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->copyMessageDuration(1500),

                SelectColumn::make('roles')
                    ->label('Hak Akses')
                    ->options(function() {
                        return \Spatie\Permission\Models\Role::whereIn('name', ['admin', 'kasir'])
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->selectablePlaceholder(false)
                    ->disabled(fn ($record) => $record->isSuperAdmin())
                    // State di-get dari ID role pertama user (sudah terscope ke tenant oleh Spatie)
                    ->state(fn ($record) => $record->roles->first()?->id)
                    // Update role menggunakan ID
                    ->updateStateUsing(function ($record, $state) {
                        $role = \Spatie\Permission\Models\Role::find($state);
                        if ($role) {
                            $record->syncRoles([$role->name]);
                        }
                        return $state;
                    }),

                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('perusahaan')
                    ->relationship('perusahaan', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Perusahaan'),

                SelectFilter::make('roles')
                    ->label('Filter Hak Akses')
                    ->relationship('roles', 'name')
                    ->preload(),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif'
                    ]),

                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah User'),
            ])
            ->recordActions([
                Impersonate::make(),
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
