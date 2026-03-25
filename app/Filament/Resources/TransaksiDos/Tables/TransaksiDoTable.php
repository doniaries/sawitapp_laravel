<?php

namespace App\Filament\Resources\TransaksiDos\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\Action as TablesAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Colors\Color;
use App\Models\TransaksiDo;

class TransaksiDoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->wrap()
                    ->badge()
                    ->color(Color::Blue),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('supir.nama')
                    ->label('Supir')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('no_polisi')
                    ->label('No Kendaraan')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->numeric()
                    ->summarize([
                        Sum::make()->suffix(' Kg')
                    ])
                    ->sortable(),

                TextColumn::make('harga_satuan')
                    ->label('Harga')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->sortable(),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->color(Color::Amber)
                    ->weight('bold')
                    ->summarize([
                        Sum::make()
                            ->numeric(0, ',', '.')
                            ->prefix('Rp ')
                    ])
                    ->sortable(),

                TextColumn::make('upah_bongkar')
                    ->label('Bongkar')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->summarize([
                        Sum::make()
                            ->numeric(0, ',', '.')
                            ->prefix('Rp ')
                    ])
                    ->sortable(),

                TextColumn::make('biaya_lain')
                    ->label('Biaya Lain/Pengambilan')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->summarize([
                        Sum::make()
                            ->numeric(0, ',', '.')
                            ->prefix('Rp ')
                    ])
                    ->sortable(),

                TextColumn::make('pembayaran_hutang')
                    ->label('Bayar Hutang')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->summarize([
                        Sum::make()
                            ->numeric(0, ',', '.')
                            ->prefix('Rp ')
                    ])
                    ->sortable(),

                TextColumn::make('sisa_bayar')
                    ->label('Sisa Bayar')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->color(Color::Green)
                    ->weight('bold')
                    ->summarize([
                        Sum::make()
                            ->numeric(0, ',', '.')
                            ->prefix('Rp ')
                    ])
                    ->sortable(),

                TextColumn::make('nominal_tunai')
                    ->label('Tunai (Split)')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->description(fn($record) => $record->cara_bayar === 'tunai & transfer' ? 'Dari total sisa bayar' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cara_bayar')
                    ->label('Metode')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tunai' => 'success',
                        'transfer' => 'info',
                        'tunai & transfer' => 'info',
                        'cair di luar' => 'warning',
                        'belum dibayar' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                TablesAction::make('refresh')
                    ->label('Refresh Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->url(fn() => \App\Filament\Resources\TransaksiDos\TransaksiDoResource::getUrl('index')),

                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Transaksi'),
            ])
            ->recordActions([
                TablesAction::make('cetak_do')
                    ->label('Cetak DO')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn(TransaksiDo $record): string => route('transaksi-do.pdf', $record))
                    ->openUrlInNewTab(),
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
            ->paginated([10, 25, 50, 100]);
    }
}
