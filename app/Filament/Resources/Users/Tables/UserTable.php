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

                TextColumn::make('roles_display')
                    ->label('Peran')
                    ->badge()
                    ->state(function (\App\Models\User $record): array {
                        // Cek superadmin dulu
                        if ($record->isSuperAdmin()) {
                            return ['super_admin'];
                        }

                        // Ambil role secara langsung dari tabel pivot untuk menghindari filter tim Spatie
                        // Kita gunakan query yang lebih fleksibel terhadap model_type
                        return \Illuminate\Support\Facades\DB::table('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('model_has_roles.model_id', $record->id)
                            ->where(function ($query) {
                                $query->where('model_has_roles.model_type', \App\Models\User::class)
                                      ->orWhere('model_has_roles.model_type', 'App\Models\User')
                                      ->orWhere('model_has_roles.model_type', 'user');
                            })
                            ->pluck('roles.name')
                            ->unique()
                            ->toArray();
                    })
                    ->color(fn ($state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'kasir' => 'info',
                        default => 'gray',
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('roles', fn ($query) => $query->where('name', 'like', "%{$search}%"));
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
