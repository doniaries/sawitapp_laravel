<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
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
                TextColumn::make('perusahaan.name')
                    ->label('Perusahaan')
                    ->sortable()
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

                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->state(function (\App\Models\User $record): array {
                        // Ambil role secara langsung dari tabel pivot untuk menghindari pengabaian oleh scope tim Spatie
                        $roles = \Illuminate\Support\Facades\DB::table('roles')
                            ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('model_has_roles.model_id', $record->id)
                            ->where('model_has_roles.model_type', \App\Models\User::class)
                            ->pluck('roles.name')
                            ->toArray();

                        if ($record->isSuperAdmin() && !in_array('super_admin', $roles)) {
                            $roles[] = 'super_admin';
                        }
                        
                        return array_unique($roles);
                    })
                    ->color(fn ($state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'kasir' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),


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

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif'
                    ]),

                TrashedFilter::make()
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
