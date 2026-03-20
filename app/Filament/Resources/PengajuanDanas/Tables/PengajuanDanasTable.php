<?php

namespace App\Filament\Resources\PengajuanDanas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class PengajuanDanasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('perusahaan.name')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('tanggal_pengajuan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('nominal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pimpinan.name')
                    ->label('Diproses Oleh')
                    ->placeholder('Belum diproses')
                    ->sortable(),
                TextColumn::make('tanggal_proses')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('bukti_transfer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'pending'),
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->form([
                        TextInput::make('bukti_transfer')
                            ->label('Link/Nomor Bukti Transfer')
                            ->required(),
                        Textarea::make('catatan_pimpinan')
                            ->label('Catatan'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'disetujui',
                            'tanggal_proses' => now(),
                            'proses_by' => Auth::id(),
                            'bukti_transfer' => $data['bukti_transfer'],
                            'catatan_pimpinan' => $data['catatan_pimpinan'],
                        ]);
                    })
                    ->requiresConfirmation(),
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('catatan_pimpinan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'tanggal_proses' => now(),
                            'proses_by' => Auth::id(),
                            'catatan_pimpinan' => $data['catatan_pimpinan'],
                        ]);
                    })
                    ->requiresConfirmation(),
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
