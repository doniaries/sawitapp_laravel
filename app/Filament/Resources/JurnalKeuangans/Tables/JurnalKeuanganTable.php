<?php

namespace App\Filament\Resources\JurnalKeuangans\Tables;



use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class JurnalKeuanganTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('tanggal')
                    ->dateTime('d F Y H:i')
                    ->label('Tanggal Transaksi')
                    ->sortable(),
                TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        default => 'primary',
                    })
                    ->searchable(),

                TextColumn::make('kategori')
                    ->searchable(),
                TextColumn::make('sub_kategori')
                    ->searchable(),
                TextColumn::make('nominal')
                    ->currency('IDR')
                    ->summarize([
                        Sum::make()
                            ->currency('IDR')
                    ])
                    ->sortable(),
                TextColumn::make('sumber_transaksi')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('nomor_referensi')
                    ->label('Nomor')
                    ->badge()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor DO berhasil disalin')
                    ->searchable(),
                TextColumn::make('pihak_terkait')
                    ->label('Nama')
                    ->formatStateUsing(function ($record) {
                        return match ($record->tipe_pihak?->value) {
                            'supir' => $record->supir?->nama ?? $record->pihak_terkait,
                            'pekerja' => $record->pekerja?->nama ?? $record->pihak_terkait,
                            'penjual' => $record->penjual?->nama ?? $record->pihak_terkait,
                            'user' => $record->user?->name ?? $record->pihak_terkait,
                            default => $record->pihak_terkait
                        };
                    })
                    ->searchable([
                        'pihak_terkait',
                        'supir.nama',
                        'pekerja.nama',
                        'penjual.nama',
                        'user.name'
                    ])
                    ->badge(),
                TextColumn::make('tipe_pihak')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => $state?->getLabel())
                    ->badge()
                    ->searchable(),

                TextColumn::make('cara_pembayaran')
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('cetakRekap')
                    ->label(fn($livewire) => 'Cetak Laporan ' . ($livewire->activeTab === 'hari_ini' ? 'Hari Ini' : ($livewire->activeTab === 'bulan_ini' ? 'Bulan Ini' : 'Terpilih')))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn($livewire) => route('jurnal-keuangan.rekap', ['tab' => $livewire->activeTab]))
                    ->openUrlInNewTab()
                    ->hidden(fn($livewire) => $livewire->activeTab === 'semua'),

                Action::make('cetakLaporan')
                    ->label('Preview Laporan (Rentang)')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->required()
                                    ->default(now()->startOfMonth())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                DatePicker::make('end_date')
                                    ->label('Sampai Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                            ])
                            ->columns(2)
                    ])
                    ->action(function (array $data) {
                        $url = route('jurnal-keuangan.rekap', [
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                        ]);

                        // Gunakan script untuk buka tab baru karena redirect() biasa tidak bisa open in new tab
                        return redirect()->to($url);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('jenis_transaksi')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran'
                    ])
                    ->placeholder('Semua Jenis')
                    ->label('Jenis'),

                SelectFilter::make('kategori')
                    ->options([
                        'DO' => 'DO',
                        'Operasional' => 'Operasional',
                        'Hutang' => 'Hutang',
                        'Saldo' => 'Saldo',
                    ])
                    ->placeholder('Semua Kategori')
                    ->label('Kategori'),

                SelectFilter::make('tipe_pihak')
                    ->options(\App\Enums\TipeNama::class)
                    ->placeholder('Semua Pihak')
                    ->label('Tipe Pihak'),

                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->native(false)
                            ->default(now()->startOfMonth()),
                        DatePicker::make('sampai_tanggal')
                            ->label('Ke Tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->native(false)
                            ->default(now())
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date));
                    }),
                TrashedFilter::make()
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(fn(Action $action) => $action->button()->label('Filter Tanggal'))
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
