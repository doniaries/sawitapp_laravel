<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Perusahaan;
use Filament\Tables\Table;
use App\Events\SaldoUpdated;
use App\Models\LaporanKeuangan;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PerusahaanResource\Pages;
use Illuminate\Database\Eloquent\Factories\Relationship;
use App\Filament\Resources\PerusahaanResource\RelationManagers;
use Filament\Tables\Columns\ImageColumn;
use App\Enums\TipeNama;

class PerusahaanResource extends Resource
{
    protected static ?string $model = Perusahaan::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(['default' => 1])
                ->schema([
                    Section::make('Informasi Dasar')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('saldo')
                                        ->required()
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->required()
                                        ->prefix('Rp.'),
                                    Forms\Components\TextInput::make('alamat')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->email(),
                                    Forms\Components\TextInput::make('pimpinan')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('npwp')
                                        ->maxLength(30),
                                    Forms\Components\FileUpload::make('logo')
                                        ->label('Logo Perusahaan')
                                        ->image()
                                        ->disk('public')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->preserveFilenames(),
                                    // ->imagePreviewHeight('250')
                                    // ->loadingIndicatorPosition('left')
                                    // ->removeUploadedFileButtonPosition('right')
                                    // ->uploadButtonPosition('left')
                                    // ->uploadProgressIndicatorPosition('left')
                                    // ->columnSpanFull(),

                                    Forms\Components\Toggle::make('is_active')
                                        ->required(),
                                ]),
                        ]),



                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(40)
                    ->defaultImageUrl(url('/images/default-logo.png'))
                    ->getStateUsing(fn($record) => $record->logo ?? '/images/default-logo.png'),
                Tables\Columns\TextColumn::make('saldo')
                    ->weight('5')
                    ->badge()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->hidden()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->hidden()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pimpinan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('npwp')
                    ->hidden()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Action::make('tambah_saldo')
                    // ->hidden()
                    ->label('Tambah Saldo')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Tambah Saldo Perusahaan')
                    ->modalDescription('Masukkan jumlah saldo yang akan ditambahkan')
                    ->form([
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

                                        // Perbaikan Select untuk cara bayar
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
                                        Forms\Components\FileUpload::make('bukti_tambah_saldo')
                                            ->label('Upload Bukti')
                                            ->image()
                                            ->disk('public')
                                            ->directory('bukti-saldo')
                                        // ->required(fn(Get $get) => $get('cara_bayar') === 'transfer')

                                    ])
                                    ->columns(1)
                            ])
                    ])
                    ->action(static function (Perusahaan $record, array $data): void {
                        try {

                            // // Validasi file upload di awal
                            // if (isset($data['bukti_tambah_saldo']) && $data['bukti_tambah_saldo']->getSize() > 1024 * 1024) {
                            //     throw new \Exception('Ukuran file melebihi batas 3MB');
                            // }

                            DB::beginTransaction();
                            // Buat event untuk refresh widget

                            // Format nominal
                            $nominal = (int)str_replace(['.', ','], '', $data['nominal']);


                            $record->increment('saldo', $nominal);
                            event(new SaldoUpdated($nominal));


                            // Catat di laporan keuangan
                            LaporanKeuangan::create([
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
                                // 'tipe_pihak' => 'pimpinan',
                                'cara_pembayaran' => $data['cara_bayar'],
                                'keterangan' => $data['keterangan'],
                                'bukti_tambah_saldo' => $data['bukti_tambah_saldo'] ?? null,
                                'mempengaruhi_kas' => $data['cara_bayar'] === 'tunai'
                            ]);

                            DB::commit();

                            // Emit event untuk refresh widgets
                            event(new SaldoUpdated($nominal));

                            Notification::make()
                                ->title('Berhasil Tambah Saldo')
                                ->success()
                                ->duration(3000) // Set durasi 3 detik
                                ->persistent(false) // Notifikasi akan otomatis hilang
                                ->body(sprintf(
                                    "Saldo bertambah Rp %s\nCara bayar: %s\nSaldo akhir: Rp %s",
                                    number_format($nominal, 0, ',', '.'),
                                    $data['cara_bayar'],
                                    number_format($record->fresh()->saldo, 0, ',', '.')
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
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RiwayatSaldoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerusahaans::route('/'),
            'create' => Pages\CreatePerusahaan::route('/create'),
            'edit' => Pages\EditPerusahaan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
