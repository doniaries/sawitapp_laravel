<?php

namespace App\Filament\Resources\TransaksiDos\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Actions\Action as TablesAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use App\Models\TransaksiDo;
use App\Traits\HasCurrencyInput;

class TransaksiDoTable
{
    use HasCurrencyInput;
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->translatedFormat('d F Y'))
                    ->sortable(),

                TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->wrap()
                    ->badge()
                    ->color(Color::Blue),

                \Filament\Tables\Columns\CheckboxColumn::make('is_mismatch')
                    ->label('Status Data')
                    ->tooltip('Centang jika data ini meragukan atau salah')
                    ->alignCenter(),



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
                    ->label('No Pol')
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->wrap(),

                TextColumn::make('harga_satuan')
                    ->label('Harga')
                    ->weight('bold')
                    ->color(Color::Blue)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->suffix(' Kg')
                            ->visible(function ($livewire) {
                                $filter = $livewire->getTableFilterState('tanggal_range');

                                return ($filter['dari_tanggal'] ?? null) || ($filter['sampai_tanggal'] ?? null);
                            })
                    ),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(Color::Amber)
                    ->weight('bold')
                    ->sortable(),


                TextColumn::make('upah_bongkar')
                    ->label('Upah Bongkar')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->currency('IDR', true)
                            ->visible(function ($livewire) {
                                $filter = $livewire->getTableFilterState('tanggal_range');

                                return ($filter['dari_tanggal'] ?? null) || ($filter['sampai_tanggal'] ?? null);
                            })
                    ),

                TextColumn::make('biaya_lain')
                    ->label('Biaya Lain/Pengambilan')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->currency('IDR', true)
                            ->visible(function ($livewire) {
                                $filter = $livewire->getTableFilterState('tanggal_range');

                                return ($filter['dari_tanggal'] ?? null) || ($filter['sampai_tanggal'] ?? null);
                            })
                    ),


                TextColumn::make('pembayaran_hutang')
                    ->label('Bayar Hutang')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->currency('IDR', true)
                            ->visible(function ($livewire) {
                                $filter = $livewire->getTableFilterState('tanggal_range');

                                return ($filter['dari_tanggal'] ?? null) || ($filter['sampai_tanggal'] ?? null);
                            })
                    ),


                TextColumn::make('sisa_bayar')
                    ->label('Sisa Bayar')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(Color::Green)
                    ->weight('bold')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->currency('IDR', true)
                            ->visible(function ($livewire) {
                                $filter = $livewire->getTableFilterState('tanggal_range');

                                return ($filter['dari_tanggal'] ?? null) || ($filter['sampai_tanggal'] ?? null);
                            })
                    ),


                TextColumn::make('nominal_tunai')
                    ->label('Tunai (Split)')
                    ->description(fn($record) => $record->cara_bayar === 'tunai & transfer' ? 'Dari total sisa bayar' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\SelectColumn::make('cara_bayar')
                    ->label('Metode')
                    ->options(\App\Models\TransaksiDo::CARA_BAYAR)
                    ->selectablePlaceholder(false)
                    ->searchable(),

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
                            ->displayFormat('d/m/Y')
                            // ->default(today())
                            ->live(),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            // ->default(today())
                            ->live(),
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
            ])
            ->headerActions([
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
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    \Filament\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
