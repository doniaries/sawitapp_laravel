<?php

namespace App\Filament\Resources\Lansirs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

class LansirsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                TextColumn::make('tanggal_lansir')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('nama_supir')
                    ->label('Supir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_penjual')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->numeric(0, ',', '.')
                    ->suffix(' Kg')
                    ->alignment('right')
                    ->sortable(),
                TextColumn::make('harga_satuan')
                    ->label('Harga')
                    ->currency('IDR')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency('IDR')
                    ->weight('bold')
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('upah')
                    ->label('Upah Supir')
                    ->currency('IDR')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
                \Filament\Tables\Filters\Filter::make('tanggal_lansir')
                    ->form([
                        DatePicker::make('dari'),
                        DatePicker::make('sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn($query) => $query->whereDate('tanggal_lansir', '>=', $data['dari']))
                            ->when($data['sampai'], fn($query) => $query->whereDate('tanggal_lansir', '<=', $data['sampai']));
                    })
                    ->label('Filter Tanggal')
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                \Filament\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                    ForceDeleteBulkAction::make()->label('Hapus Permanen'),
                    RestoreBulkAction::make()->label('Pulihkan'),
                ]),
            ]);
    }
}
