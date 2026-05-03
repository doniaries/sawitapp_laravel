<?php

namespace App\Filament\Resources\JurnalKeuangans\Tables;



use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
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
                Action::make('cetakLaporan')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->modalHeading('Cetak Laporan Keuangan')
                    ->modalDescription('Silakan pilih rentang tanggal laporan yang ingin dicetak.')
                    ->modalSubmitActionLabel('Cetak / Download PDF')
                    ->modalWidth('6xl')
                    ->form([
                        FormGrid::make(3)
                            ->schema([
                                Select::make('rentang')
                                    ->label('Pilih Rentang')
                                    ->options([
                                        'hari_ini' => 'Hari Ini',
                                        'bulan_ini' => 'Bulan Ini',
                                        'custom' => 'Rentang Custom',
                                    ])
                                    ->default('hari_ini')
                                    ->live()
                                    ->required(),
                                
                                DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->default(now()->startOfMonth())
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->visible(fn (Get $get) => $get('rentang') === 'custom')
                                    ->live()
                                    ->required(),
                                
                                DatePicker::make('end_date')
                                    ->label('Sampai Tanggal')
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->visible(fn (Get $get) => $get('rentang') === 'custom')
                                    ->live()
                                    ->required(),
                            ]),

                        FormSection::make('Pratinjau Laporan')
                            ->description('Hasil cetak akan terlihat seperti di bawah ini')
                            ->schema([
                                Placeholder::make('pdf_preview')
                                    ->hiddenLabel()
                                    ->content(function (Get $get) {
                                        $rentang = $get('rentang');
                                        $dari = $get('start_date') ?? date('Y-m-d');
                                        $sampai = $get('end_date') ?? date('Y-m-d');

                                        if ($rentang === 'hari_ini') {
                                            $dari = $sampai = date('Y-m-d');
                                        } elseif ($rentang === 'bulan_ini') {
                                            $dari = date('Y-m-01');
                                            $sampai = date('Y-m-t');
                                        }

                                        $url = route('jurnal-keuangan.rekap', [
                                            'start_date' => $dari,
                                            'end_date' => $sampai,
                                            't' => time()
                                        ]);

                                        return new HtmlString("
                                            <div class='w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden bg-white dark:bg-gray-900 shadow-inner' style='height: 750px;'>
                                                <embed src='{$url}#toolbar=1&navpanes=0&scrollbar=1' type='application/pdf' width='100%' height='100%' />
                                            </div>
                                            <div class='mt-2 text-center text-xs text-gray-500'>
                                                Tips: Gunakan tombol print di pojok kanan atas pratinjau untuk mencetak langsung.
                                            </div>
                                        ");
                                    }),
                            ])
                            ->collapsible(),
                    ])
                    ->action(function (array $data) {
                        $params = ['tab' => $data['rentang'], 'download' => 1];
                        if ($data['rentang'] === 'custom') {
                            $params = [
                                'start_date' => $data['start_date'],
                                'end_date' => $data['end_date'],
                                'download' => 1
                            ];
                        }
                        return redirect()->to(route('jurnal-keuangan.rekap', $params));
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
                Action::make('previewKwitansi')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('warning')
                    ->modalHeading('Pratinjau Kwitansi DO')
                    ->modalWidth('5xl')
                    ->visible(fn ($record) => $record->kategori === 'DO' && $record->referensi_id)
                    ->modalSubmitActionLabel('Download PDF')
                    ->schema([
                        Placeholder::make('pdf_kwitansi')
                            ->label('Kwitansi DO')
                            ->content(function ($record) {
                                $url = route('transaksi-do.pdf', ['id' => $record->referensi_id]);
                                return new HtmlString("
                                    <div style='width: 100%; border: 1px solid #444; border-radius: 8px; overflow: hidden;'>
                                        <iframe src='{$url}' style='width: 100%; height: 60vh; border: none;'></iframe>
                                    </div>
                                ");
                            }),
                    ])
                    ->action(function ($record) {
                        return redirect()->to(route('transaksi-do.pdf', ['id' => $record->referensi_id, 'download' => 1]));
                    }),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
