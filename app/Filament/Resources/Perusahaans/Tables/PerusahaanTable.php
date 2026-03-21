<?php

namespace App\Filament\Resources\Perusahaans\Tables;

use App\Models\Perusahaan;
use App\Models\JurnalKeuangan;
use App\Enums\TipeNama;
use App\Events\SaldoUpdated;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PerusahaanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(40)
                    ->defaultImageUrl(url('/images/default-logo.png'))
                    ->getStateUsing(fn($record) => $record->logo ?? '/images/default-logo.png'),
                TextColumn::make('saldo')
                    ->weight('5')
                    ->badge()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('alamat')
                    ->sortable(),
                TextColumn::make('telepon')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('email')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('pimpinan')
                    ->searchable(),
                TextColumn::make('npwp')
                    ->hidden()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Perusahaan'),
            ])
            ->recordActions([
                Action::make('tambah_saldo')
                    ->label('Tambah Saldo')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Tambah Saldo Perusahaan')
                    ->modalDescription('Masukkan jumlah saldo yang akan ditambahkan')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        DatePicker::make('tanggal')
                                            ->label('Tanggal')
                                            ->default(now())
                                            ->required(),

                                        TextInput::make('nominal')
                                            ->label('Nominal')
                                            ->numeric()
                                            ->required()
                                            ->currencyMask(
                                                thousandSeparator: '.',
                                                decimalSeparator: ',',
                                                precision: 0,
                                            )
                                            ->prefix('Rp'),

                                        Select::make('cara_bayar')
                                            ->label('Cara Bayar')
                                            ->options([
                                                'tunai' => 'tunai',
                                                'transfer' => 'transfer'
                                            ])
                                            ->required()
                                            ->default('tunai')
                                            ->live(),

                                        Textarea::make('keterangan')
                                            ->label('Keterangan')
                                            ->placeholder('Sumber dana / keterangan lainnya')
                                            ->rows(3),
                                        FileUpload::make('bukti_tambah_saldo')
                                            ->label('Upload Bukti')
                                            ->image()
                                            ->disk('public')
                                            ->directory('bukti-saldo')
                                    ])
                                    ->columns(1)
                            ])
                    ])
                    ->action(static function (Perusahaan $record, array $data): void {
                        try {
                            DB::beginTransaction();

                            $nominal = (int)str_replace(['.', ','], '', $data['nominal']);

                            $record->increment('saldo', $nominal);
                            event(new SaldoUpdated($nominal));

                            JurnalKeuangan::create([
                                'perusahaan_id' => $record->id,
                                'tanggal' => $data['tanggal'],
                                'jenis_transaksi' => 'Pemasukan',
                                'kategori' => 'Saldo',
                                'tipe_pihak' => TipeNama::USER->value,
                                'sub_kategori' => 'Tambah Saldo',
                                'nominal' => $nominal,
                                'sumber_transaksi' => 'Perusahaan',
                                'referensi_id' => $record->id,
                                'nomor_referensi' => 'TBS-' . now()->format('Ymd-His'),
                                'pihak_terkait' => $record->pimpinan,
                                'cara_pembayaran' => $data['cara_bayar'],
                                'keterangan' => $data['keterangan'],
                                'bukti_tambah_saldo' => $data['bukti_tambah_saldo'] ?? null,
                                'mempengaruhi_kas' => $data['cara_bayar'] === 'tunai'
                            ]);

                            DB::commit();

                            event(new SaldoUpdated($nominal));

                            Notification::make()
                                ->title('Berhasil Tambah Saldo')
                                ->success()
                                ->duration(3000)
                                ->body(sprintf(
                                    "Saldo bertambah Rp %s\nCara bayar: %s\nSaldo akhir: Rp %s",
                                    number_format((float) $nominal, 0, ',', '.'),
                                    $data['cara_bayar'],
                                    number_format((float) $record->fresh()->saldo, 0, ',', '.')
                                ))
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->danger()
                                ->title('Gagal Tambah Saldo')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalWidth('lg'),
                EditAction::make(),
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
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
