<?php

namespace App\Filament\Resources\TambahSaldos\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action as NotificationAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Enums\TipeNama;

class TambahSaldosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d F Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Entry Oleh')
                    ->searchable(),
                TextColumn::make('nominal')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                TrashedFilter::make(),
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
            ]);
    }
}
