<?php

namespace App\Filament\Resources\TransaksiDos\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use App\Models\TransaksiDo;
use App\Models\TutupHari;
use App\Models\TransaksiOperasional;
use App\Models\Perusahaan;
use App\Traits\HasCurrencyInput;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiDoTable
{
    use HasCurrencyInput;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state instanceof \Carbon\Carbon ? $state->translatedFormat('d F Y') : \Carbon\Carbon::parse($state)->translatedFormat('d F Y'))
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
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
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
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
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
                            ->default(today())
                            ->live(),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(today())
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
                    ->label('Tambah Transaksi')
                    ->visible(
                        fn() =>
                        Auth::user()->isSuperAdmin() ||
                            !TutupHari::isClosed(today(), Filament::getTenant()->id)
                    ),
                CreateAction::make('tutup_hari')
                    ->label('Tutup Hari')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penutupan Hari')
                    ->modalDescription('Apakah Anda yakin ingin melakukan ini?')
                    ->modalSubmitActionLabel('Konfirmasi & Tutup Hari')
                    ->modalWidth('5xl')
                    ->form([
                        Grid::make(3)
                            ->schema([
                                ViewEntry::make('full_summary')
                                    ->label(null)
                                    ->content(fn($get) => self::getSummaryTableHtml($get('tanggal')))
                                    ->columnSpan(2),

                                Grid::make(1)
                                    ->schema([
                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Tutup')
                                            ->default(now())
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->disabledDates(function () {
                                                $perusahaanId = Filament::getTenant()->id;
                                                return Tutuphari::where('perusahaan_id', '=', $perusahaanId, 'and')
                                                    ->pluck('tanggal')
                                                    ->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString())
                                                    ->toArray();
                                            })
                                            ->rules([
                                                fn() => function (string $attribute, $value, \Closure $fail) {
                                                    if (Auth::user()->isSuperAdmin()) return;
                                                    $perusahaanId = Filament::getTenant()->id;
                                                    if (TutupHari::isClosed($value, $perusahaanId)) {
                                                        $fail('Tanggal ini sudah ditutup sebelumnya.');
                                                    }
                                                },
                                            ]),
                                        TextInput::make('saldo_akhir_fisik')
                                            ->label('Saldo Kas Fisik')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->default(0),
                                        Textarea::make('catatan')
                                            ->label('Catatan'),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $tanggal = $data['tanggal'];
                        $perusahaanId = Filament::getTenant()->id;

                        if (TutupHari::isClosed($tanggal, $perusahaanId)) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Tanggal ini sudah ditutup sebelumnya.')
                                ->danger()
                                ->send();
                            return;
                        }

                        TutupHari::performClosing($data, $perusahaanId);

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Hari ini (" . \Carbon\Carbon::parse($tanggal)->format('d/m/Y') . ") telah ditutup.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('cetak_do')
                    ->label('Cetak DO')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn(TransaksiDo $record): string => route('transaksi-do.pdf', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->recordBulkActions([
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
    public static function getSummaryTableHtml($tanggal = null): HtmlString
    {
        $perusahaanId = Filament::getTenant()->id;
        $tanggal = $tanggal ?: today()->toDateString();

        // 1. Ambil Data DO
        $doQuery = TransaksiDo::query()
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal);

        $doCount = $doQuery->count();
        $doTotal = $doQuery->sum('sub_total');

        // 2. Ambil Data Operasional (Pengeluaran)
        $opQuery = TransaksiOperasional::query()
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal);

        $opCount = $opQuery->count();
        $opTotal = $opQuery->sum('nominal');

        // 3. Ambil Data Rekonsiliasi Kas (Pemasukan vs Pengeluaran)
        $perusahaan = Perusahaan::find($perusahaanId);

        $masuk = DB::table('jurnal_keuangans')
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pemasukan')
            ->where('cara_pembayaran', 'tunai')
            ->sum('nominal');

        $keluar = DB::table('jurnal_keuangans')
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pengeluaran')
            ->where('cara_pembayaran', 'tunai')
            ->sum('nominal');

        $saldoSistem = (float) ($perusahaan->saldo ?? 0);
        $saldoAwal = $saldoSistem - $masuk + $keluar;

        return new HtmlString("
            <div class='overflow-hidden border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-lg bg-white dark:bg-gray-800' style='width: 100%;'>
                <table class='w-full text-base border-collapse tracking-wide' style='table-layout: fixed; width: 100%; border: 1px solid #d1d5db;'>
                    <thead>
                        <tr class='bg-gray-100 dark:bg-gray-800 border-b-2 border-gray-300 dark:border-gray-600'>
                            <th class='w-2/3 pl-4 pr-8 py-6 font-bold text-gray-800 dark:text-gray-200 uppercase text-sm tracking-widest text-left border border-gray-300 dark:border-gray-600'>Keterangan Ringkasan</th>
                            <th class='w-1/3 px-8 py-6 font-bold text-gray-800 dark:text-gray-200 uppercase text-sm tracking-widest border border-gray-300 dark:border-gray-600' style='text-align: right;'>Jumlah / Nilai</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y-2 divide-gray-300 dark:divide-gray-600'>
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-primary-500 shadow-sm'></div>
                                    TRANSAKSI PEMBELIAN BUAH (DO)
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Banyak DO Hari Ini</td>
                            <td class='px-8 py-5 font-bold text-primary-600 text-lg border border-gray-300 dark:border-gray-600' style='text-align: right;'>{$doCount} DO</td>
                        </tr>
                        <tr class='hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Total Pembelian DO</td>
                            <td class='px-8 py-5 font-bold text-primary-600 font-mono text-xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($doTotal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-t-2 border-gray-300 dark:border-gray-600 border-b'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-warning-500 shadow-sm'></div>
                                    BIAYA OPERASIONAL
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-warning-50/50 dark:hover:bg-warning-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Jumlah Item Pengeluaran</td>
                            <td class='px-8 py-5 font-bold text-primary-600 text-lg border border-gray-300 dark:border-gray-600' style='text-align: right;'>{$opCount} Item</td>
                        </tr>
                        <tr class='hover:bg-warning-50/50 dark:hover:bg-warning-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-gray-600 dark:text-gray-400 font-medium text-left border border-gray-300 dark:border-gray-600'>Total Biaya Operasional</td>
                            <td class='px-8 py-5 font-bold text-primary-600 font-mono text-xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($opTotal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-gray-50/80 dark:bg-gray-900/40 border-t-2 border-gray-300 dark:border-gray-600 border-b'>
                            <td class='pl-4 pr-8 py-4 text-gray-900 dark:text-white font-black uppercase text-sm border border-gray-300 dark:border-gray-600' colspan='2'>
                                <div class='flex items-center gap-3'>
                                    <div class='w-3 h-3 rounded-full bg-success-500 shadow-sm'></div>
                                    REKONSILIASI KAS (SISTEM)
                                </div>
                            </td>
                        </tr>
                        <tr class='hover:bg-success-50/50 dark:hover:bg-success-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-gray-700 dark:text-gray-200 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Saldo Awal (Kas Tunai)</td>
                            <td class='px-8 py-5 font-bold font-mono text-primary-700 dark:text-primary-400 text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($saldoAwal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='hover:bg-success-50/50 dark:hover:bg-success-900/10 transition duration-150 border-b border-gray-200'>
                            <td class='pl-4 pr-8 py-5 text-success-700 dark:text-success-400 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Total Pemasukan Tunai</td>
                            <td class='px-8 py-5 font-bold text-primary-700 dark:text-primary-400 font-mono text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($masuk, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='hover:bg-danger-50/50 dark:hover:bg-danger-900/10 transition duration-150 border-b border-gray-300'>
                            <td class='pl-4 pr-8 py-5 text-danger-700 dark:text-danger-400 font-semibold text-xl text-left border border-gray-300 dark:border-gray-600'>Total Pengeluaran Tunai</td>
                            <td class='px-8 py-5 font-bold text-primary-700 dark:text-primary-400 font-mono text-3xl border border-gray-300 dark:border-gray-600' style='text-align: right;'>Rp " . number_format($keluar, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-primary-50/80 dark:bg-primary-900/20 border-t-4 border-primary-500 shadow-inner'>
                            <td class='pl-4 pr-8 py-10 !font-black !text-primary-900 dark:!text-primary-100 uppercase text-3xl tracking-widest text-left border-r border-primary-200 dark:border-primary-700 border border-gray-300'>SALDO AKHIR SISTEM</td>
                            <td class='px-8 py-10 !font-black font-mono border border-gray-300 dark:border-gray-600' style='text-align: right; font-weight: 900 !important; font-size: 30px !important; color: #2563eb !important;'>Rp " . number_format($saldoSistem, 0, ',', '.') . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        ");
    }
}
