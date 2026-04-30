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
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
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
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->translatedFormat('d F Y H:i'))
                    ->sortable(),

                TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->wrap()
                    ->badge()
                    ->color(Color::Blue),

                IconColumn::make('is_mismatch')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn($record) => $record->is_mismatch ? 'Hitungan Sistem Tidak Cocok' : 'Data Cocok/Sesuai'),

                ImageColumn::make('bukti_rekap')
                    ->label('Bukti')
                    ->disk('public')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('supir.nama')
                    ->label('Supir')
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('no_polisi')
                    ->label('No Kendaraan')
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->wrap(),

                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->numeric(0, ',', '.')
                    ->summarize([
                        Sum::make()->suffix(' Kg')
                            ->numeric(0, ',', '.')
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
            ->filters([
                TrashedFilter::make(),
                Filter::make('tanggal_range')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d/m/Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ], layout: FiltersLayout::Modal)
            ->headerActions([
                TablesAction::make('filter_periode')
                    ->label('Filter Periode')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(today()),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(today()),
                    ])
                    ->action(function (array $data, $livewire) {
                        $livewire->tableFilters['tanggal_range'] = [
                            'dari_tanggal' => $data['dari_tanggal'],
                            'sampai_tanggal' => $data['sampai_tanggal'],
                        ];
                    }),
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
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
