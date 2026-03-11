<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Penjual;
use Filament\Forms\Form;
use App\Models\Kendaraan;
use Filament\Tables\Table;
use App\Models\TransaksiDo;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiDoResource\Pages;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;

use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class TransaksiDoResource extends Resource
{
    protected static ?string $model = TransaksiDo::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?string $modelLabel = 'Transaksi DO';
    protected static ?string $pluralModelLabel = 'Transaksi DO';
    protected static ?string $pollingInterval = '15s';
    protected static ?int $navigationSort = 1;


    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['penjual', 'supir', 'kendaraan'])
            ->currentMonth()
            ->latest('tanggal');
    }

    public static function getWidgets(): array
    {
        return [
            TransaksiDoStatWidget::class,
        ];
    }

    public static function getModel(): string
    {
        return TransaksiDo::class;
    }



    public static function form(Form $form): Form
    {
        return $form->schema([
            // Header Section - Informasi Utama
            Forms\Components\Section::make()
                ->schema([

                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(function () {
                                    return TransaksiDo::generateMonthlyNumber();
                                })
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->hint('Pilih Tanggal')
                                ->hintColor('primary')
                                // ->autofocus()
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i:s')
                                ->default(Carbon::now()) // Menggunakan Carbon untuk nilai default
                                ->required()
                                // ->disabled()
                                ->dehydrated(),

                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),

            // Detail Pengiriman Section
            Forms\Components\Grid::make()
                ->schema([
                    // Panel Kiri
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Select::make('penjual_id')
                                        ->label('Penjual')
                                        ->relationship('penjual', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('nama')
                                                ->label('Nama Penjual')
                                                ->unique(ignoreRecord: true)
                                                ->required()
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('alamat')
                                                ->label('Alamat')
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('telepon')
                                                ->tel()
                                                ->label('Nomor Telepon'),

                                            // Hutang awal hanya muncul saat create
                                            Forms\Components\TextInput::make('hutang')
                                                ->label(fn($context) => $context === 'create' ?
                                                    'Hutang Awal' : 'Total Hutang')
                                                ->helperText(fn($context) => $context === 'create' ?
                                                    'Masukkan hutang awal jika ada. Input ini hanya bisa dilakukan sekali saat pendaftaran penjual.' : '')
                                                // ->disabled(fn($context) => $context !== 'create')
                                                ->dehydrated()
                                                ->prefix('Rp')
                                                ->numeric()
                                                ->default(0)
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0
                                                ),
                                        ])

                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state) {
                                                // Ambil data penjual
                                                $penjual = \App\Models\Penjual::find($state);
                                                if ($penjual) {
                                                    // Set hutang awal dan sisa hutang
                                                    $set('hutang_awal', $penjual->hutang);
                                                    $set('sisa_hutang_penjual', $penjual->hutang);
                                                    $set('pembayaran_hutang', 0);

                                                    // Log untuk tracking
                                                    Log::info('Auto-fill supir saat pemilihan penjual:', [
                                                        'penjual_id' => $state,
                                                        'penjual_nama' => $penjual->nama,
                                                    ]);

                                                    // // Notifikasi ke user
                                                    // Notification::make()
                                                    //     ->title('Penjual Dipilih')
                                                    //     ->body('Data penjual diisi otomatis')
                                                    //     ->success()
                                                    //     ->duration(3000)
                                                    //     ->send();
                                                }
                                            }
                                        }),


                                    Forms\Components\Select::make('supir_id')
                                        ->label('Supir')
                                        ->relationship('supir', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            // Reset kendaraan when supir changes
                                            $set('kendaraan_id', null);
                                        })
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('nama')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('alamat')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('telepon')
                                                ->tel()
                                                ->maxLength(255),
                                        ]),

                                    Forms\Components\Select::make('kendaraan_id')
                                        ->label('Nomor Polisi')
                                        ->placeholder('Pilih atau tambah kendaraan')
                                        ->options(function (Forms\Get $get) {
                                            $supirId = $get('supir_id');
                                            if (!$supirId) return [];

                                            return Kendaraan::query()
                                                ->where('supir_id', $supirId)
                                                ->pluck('no_polisi', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('no_polisi')
                                                ->label('Nomor Polisi')
                                                ->required()
                                                ->unique(Kendaraan::class, 'no_polisi') // Perbaikan unique constraint
                                                ->maxLength(10)
                                                ->placeholder('BA 1234 XX'),

                                            Forms\Components\Hidden::make('supir_id')
                                                ->default(function (Forms\Get $get) {
                                                    return $get('../../supir_id');
                                                })
                                        ])
                                        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                            return $action
                                                ->modalHeading('Tambah Kendaraan Baru')
                                                ->modalWidth('lg')
                                                ->modalButton('Simpan')
                                                ->successNotification(
                                                    Notification::make()
                                                        ->success()
                                                        ->title('Kendaraan berhasil ditambahkan')
                                                        ->duration(3000)
                                                );
                                        })
                                        ->createOptionUsing(function (array $data, Forms\Get $get) {
                                            DB::beginTransaction();
                                            try {
                                                // Pastikan data valid
                                                if (empty($data['no_polisi'])) {
                                                    throw new \Exception('Nomor polisi harus diisi');
                                                }

                                                // Format nomor polisi
                                                $noPolisi = strtoupper(trim($data['no_polisi']));

                                                // Ambil supir_id dari form utama jika tidak ada di data
                                                $supirId = $data['supir_id'] ?? $get('supir_id');

                                                if (!$supirId) {
                                                    throw new \Exception('Data supir tidak ditemukan');
                                                }

                                                // Cek duplikasi
                                                $exists = Kendaraan::where('no_polisi', $noPolisi)->exists();
                                                if ($exists) {
                                                    throw new \Exception('Nomor polisi sudah terdaftar');
                                                }

                                                // Buat kendaraan baru
                                                $kendaraan = Kendaraan::create([
                                                    'no_polisi' => $noPolisi,
                                                    'supir_id' => $supirId
                                                ]);

                                                DB::commit();

                                                // Log untuk tracking
                                                Log::info('Kendaraan baru berhasil dibuat:', [
                                                    'no_polisi' => $noPolisi,
                                                    'supir_id' => $supirId,
                                                    'kendaraan_id' => $kendaraan->id
                                                ]);

                                                return $kendaraan->id;
                                            } catch (\Exception $e) {
                                                DB::rollBack();
                                                Log::error('Error saat membuat kendaraan:', [
                                                    'error' => $e->getMessage(),
                                                    'data' => $data
                                                ]);

                                                Notification::make()
                                                    ->danger()
                                                    ->title('Gagal menambahkan kendaraan')
                                                    ->body($e->getMessage())
                                                    ->duration(3000)
                                                    ->send();

                                                throw $e;
                                            }
                                        })
                                        ->helperText('Pilih kendaraan yang ada atau tambah baru'),

                                    Forms\Components\TextInput::make('tonase')
                                        ->label('Tonase (Netto)')
                                        ->hintIcon('heroicon-m-exclamation-triangle')
                                        ->hintColor('primary')
                                        // ->hint('angka tanpa titik')
                                        ->required()
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->numeric()
                                        ->suffix('Kg')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            // Ambil penjual_id yang sudah dipilih sebelumnya
                                            $penjualId = $get('penjual_id');

                                            // Jika tonase sudah diisi dan penjual sudah dipilih
                                            if ($state && $penjualId) {
                                                $penjual = \App\Models\Penjual::find($penjualId);
                                                if ($penjual) {
                                                    // Cek apakah supir_id sudah diisi
                                                    $existingSupirId = $get('supir_id');

                                                    if (!$existingSupirId) {
                                                        // Auto create/find supir berdasarkan penjual
                                                        $supir = \App\Models\Supir::firstOrCreate(
                                                            ['nama' => $penjual->nama],
                                                            [
                                                                'alamat' => $penjual->alamat ?? '',
                                                                'telepon' => $penjual->telepon ?? '',
                                                            ]
                                                        );

                                                        // Set supir_id setelah tonase diisi
                                                        $set('supir_id', $supir->id);

                                                        // Log untuk tracking
                                                        \Log::info('Auto-fill supir setelah tonase diisi:', [
                                                            'penjual_id' => $penjualId,
                                                            'penjual_nama' => $penjual->nama,
                                                            'supir_id' => $supir->id,
                                                            'tonase' => $state
                                                        ]);

                                                        // Notifikasi ke user
                                                        Notification::make()
                                                            ->title('Supir Ditambahkan')
                                                            ->body('Jika data Supir kosong, akan diisi otomatis dengan nama penjual,setelah mengisi tonase!')
                                                            ->success()
                                                            ->duration(5000)
                                                            ->send();
                                                    }
                                                }
                                            }

                                            // Panggil fungsi hitung total
                                            static::hitungTotal($state, $get, $set);
                                        })
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungTotal($state, $get, $set)),

                                    Forms\Components\TextInput::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->required()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->numeric()

                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungTotal($state, $get, $set)),
                                ])
                                ->columns(3),
                        ])
                        ->columnSpan(2),

                    // Panel Kanan
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('hutang_awal')
                                ->label('Total Hutang')
                                ->currencyMask(
                                    thousandSeparator: '.',
                                    decimalSeparator: ',',
                                    precision: 0
                                )
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated()
                                ->numeric()
                                ->default(0),
                            Forms\Components\TextInput::make('sub_total')
                                ->label('Sub Total')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->prefix('Rp')
                                ->extraAttributes(['class' => 'text-xl font-bold text-primary-600'])
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Perhitungan & Pembayaran Section
            Forms\Components\Grid::make()
                ->schema([
                    // Panel Kiri - Detail Pembayaran
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('upah_bongkar')
                                        ->label('Upah Bongkar')
                                        // ->hidden() //sembunyikan
                                        ->hint('*jika ada')
                                        ->hintColor('primary')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungSisaBayar($state, $get, $set)),

                                    Forms\Components\TextInput::make('biaya_lain')
                                        ->label('Biaya')
                                        ->hint('*jika ada')
                                        ->hintColor('primary')
                                        ->currencyMask(
                                            thousandSeparator: ',',
                                            decimalSeparator: '.',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            try {
                                                // Format nilai
                                                $subTotal = self::formatCurrency($get('sub_total'));
                                                $upahBongkar = self::formatCurrency($get('upah_bongkar'));
                                                $biayaLain = self::formatCurrency($state);
                                                $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));

                                                // Hitung komponen pengurangan
                                                $komponenPengurangan = $upahBongkar + $biayaLain + $bayarHutang;

                                                // Hitung sisa bayar
                                                $sisaBayar = max(0, $subTotal - $komponenPengurangan);

                                                // Update nilai sisa bayar
                                                $set('sisa_bayar', $sisaBayar);

                                                // Log untuk debugging
                                                Log::info('Perhitungan Biaya Lain:', [
                                                    'sub_total' => $subTotal,
                                                    'upah_bongkar' => $upahBongkar,
                                                    'biaya_lain' => $biayaLain,
                                                    'bayar_hutang' => $bayarHutang,
                                                    'komponen_pengurangan' => $komponenPengurangan,
                                                    'sisa_bayar' => $sisaBayar
                                                ]);
                                            } catch (\Exception $e) {
                                                Log::error('Error menghitung biaya lain:', [
                                                    'error' => $e->getMessage(),
                                                    'state' => $state
                                                ]);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('pembayaran_hutang')
                                        ->label('Bayar Hutang')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true) // Penting: gunakan onBlur untuk perhitungan yang akurat
                                        // Tambahkan kondisi visible
                                        ->visible(fn(Forms\Get $get): bool => $get('hutang_awal') > 0)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            try {
                                                // Format values
                                                $hutangAwal = self::formatCurrency($get('hutang_awal'));
                                                $bayarHutang = self::formatCurrency($state);

                                                // Validasi pembayaran tidak melebihi hutang
                                                if ($bayarHutang > $hutangAwal) {
                                                    $set('pembayaran_hutang', $hutangAwal);
                                                    $bayarHutang = $hutangAwal;

                                                    // Notifikasi untuk pembayaran yang disesuaikan
                                                    Notification::make()
                                                        ->warning()
                                                        ->title('Pembayaran Disesuaikan')
                                                        ->body(sprintf(
                                                            'Pembayaran disesuaikan menjadi Rp %s sesuai total hutang',
                                                            number_format($hutangAwal, 0, ',', '.')
                                                        ))
                                                        ->persistent()
                                                        ->actions([
                                                            \Filament\Notifications\Actions\Action::make('Tambah Saldo')
                                                                ->button()
                                                                ->url(route('filament.admin.resources.perusahaans.index'))
                                                        ])
                                                        ->send();
                                                }

                                                // Update sisa hutang penjual
                                                $sisaHutang = max(0, $hutangAwal - $bayarHutang);
                                                $set('sisa_hutang_penjual', $sisaHutang);

                                                // Hitung ulang sisa bayar
                                                $subTotal = self::formatCurrency($get('sub_total'));
                                                $biayaLain = self::formatCurrency($get('biaya_lain'));
                                                $upahBongkar = self::formatCurrency($get('upah_bongkar'));

                                                // Total pengurangan
                                                $komponenPengurangan = $upahBongkar + $biayaLain + $bayarHutang;

                                                // Hitung sisa bayar
                                                $sisaBayar = max(0, $subTotal - $komponenPengurangan);
                                                $set('sisa_bayar', $sisaBayar);

                                                // Log untuk memudahkan debug
                                                Log::info('Perhitungan Pembayaran Hutang:', [
                                                    'hutang_awal' => $hutangAwal,
                                                    'bayar_hutang' => $bayarHutang,
                                                    'sisa_hutang' => $sisaHutang,
                                                    'sub_total' => $subTotal,
                                                    'komponen_pengurangan' => $komponenPengurangan,
                                                    'sisa_bayar' => $sisaBayar
                                                ]);
                                            } catch (\Exception $e) {
                                                Log::error('Error saat menghitung pembayaran hutang:', [
                                                    'error' => $e->getMessage(),
                                                    'state' => $state
                                                ]);

                                                // Notifikasi error ke user
                                                Notification::make()
                                                    ->danger()
                                                    ->title('Error Perhitungan')
                                                    ->body('Terjadi kesalahan saat menghitung pembayaran hutang')
                                                    ->duration(3000)
                                                    ->send();
                                            }
                                        }),

                                    Forms\Components\TextInput::make('keterangan_biaya_lain')
                                        ->label('Keterangan Biaya Lain')
                                        ->hidden()
                                        // ->visible(fn($get) => $get('biaya_lain') === 'biaya_lain')
                                        ->hint('*jika ada')
                                        ->hintColor('primary')
                                        ->placeholder('misal:uang jalan'),
                                    // Forms\Components\Select::make('status_bayar')
                                    //     ->label('Status Bayar')
                                    //     ->options([
                                    //         'Lunas' => 'Lunas',
                                    //         'Belum Lunas' => 'Belum Lunas',

                                    //     ])
                                    //     ->default('Lunas')
                                    //     ->required(),
                                    Forms\Components\Select::make('cara_bayar')
                                        ->label('Cara Bayar')
                                        ->options(TransaksiDo::CARA_BAYAR)
                                        ->native(false)
                                        ->default('tunai')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if ($state !== 'tunai') {
                                                // Reset validasi saldo untuk non-tunai
                                                $set('_tmp_bypass_saldo_check', true);
                                            }
                                            // Ambil penjual_id yang sudah dipilih sebelumnya
                                            $penjualId = $get('penjual_id');

                                            // Jika cara bayar sudah dipilih dan penjual sudah dipilih
                                            if ($state && $penjualId) {
                                                $penjual = \App\Models\Penjual::find($penjualId);
                                                if ($penjual) {
                                                    // Cek apakah supir_id sudah diisi
                                                    $existingSupirId = $get('supir_id');

                                                    if (!$existingSupirId) {
                                                        // Auto create/find supir berdasarkan penjual
                                                        $supir = \App\Models\Supir::firstOrCreate(
                                                            ['nama' => $penjual->nama],
                                                            [
                                                                'alamat' => $penjual->alamat ?? '',
                                                                'telepon' => $penjual->telepon ?? '',
                                                            ]
                                                        );

                                                        // Set supir_id setelah cara bayar diisi
                                                        $set('supir_id', $supir->id);

                                                        // Log untuk tracking
                                                        \Log::info('Auto-fill supir setelah cara bayar diisi:', [
                                                            'penjual_id' => $penjualId,
                                                            'penjual_nama' => $penjual->nama,
                                                            'supir_id' => $supir->id,
                                                            'cara_bayar' => $state
                                                        ]);

                                                        // Notifikasi ke user
                                                        Notification::make()
                                                            ->title('Supir Ditambahkan')
                                                            ->body('Data supir diisi otomatis setelah memilih cara bayar')
                                                            ->success()
                                                            ->duration(3000)
                                                            ->send();
                                                    }
                                                }
                                            }
                                        }),

                                    Forms\Components\Section::make('Informasi Saldo')
                                        ->schema([
                                            Forms\Components\Placeholder::make('saldo_perusahaan')
                                                ->label('Saldo Perusahaan')
                                                ->content(function () {
                                                    $perusahaan = \App\Models\Perusahaan::first();
                                                    return 'Rp ' . number_format($perusahaan->saldo ?? 0, 0, ',', '.');
                                                })
                                                ->extraAttributes(['class' => 'text-lg font-semibold']),
                                        ]),


                                    Forms\Components\TextInput::make('catatan')
                                        ->hidden()
                                        ->label('Catatan'),

                                ])
                                ->columns(3),
                        ])
                        ->columnSpan(2),

                    // Panel Kanan - Informasi Hutang & Sisa
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('sisa_hutang_penjual')
                                        ->label('Sisa Hutang')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->disabled()
                                        ->dehydrated()
                                        ->default(0),


                                    Forms\Components\TextInput::make('sisa_bayar')
                                        ->label('Sisa Bayar')
                                        ->required()
                                        ->prefix('Rp')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get) {
                                            // Cek cara bayar dan saldo
                                            if ($get('cara_bayar') === 'tunai') {
                                                $perusahaan = \App\Models\Perusahaan::first();
                                                $saldoSaatIni = $perusahaan->saldo ?? 0;
                                                $sisaBayar = self::formatCurrency($state);

                                                if ($sisaBayar > $saldoSaatIni) {
                                                    Notification::make()
                                                        ->warning()
                                                        ->title('Saldo Tidak Mencukupi')
                                                        ->body(sprintf(
                                                            "Saldo saat ini: Rp %s\nDibutuhkan: Rp %s",
                                                            number_format($saldoSaatIni, 0, ',', '.'),
                                                            number_format($sisaBayar, 0, ',', '.')
                                                        ))
                                                        ->persistent()
                                                        ->actions([
                                                            \Filament\Notifications\Actions\Action::make('Tambah Saldo')
                                                                ->button()
                                                                ->url(route('filament.admin.resources.operasionals.index'))
                                                        ])
                                                        ->send();
                                                }
                                            }
                                        }),


                                ])
                                ->columns(1),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                // Tables\Columns\TextColumn::make('file_do') //image file do
                //     ->label('File DO')
                //     ->tooltip('klik untuk melihat')
                //     ->alignCenter()
                //     ->icon('heroicon-m-document')
                //     ->color(Color::Emerald)
                //     ->formatStateUsing(fn($state) => $state ? 'Lihat' : '-')
                //     ->action(
                //         Action::make('previewFile')
                //             ->modalHeading('Preview File DO')
                //             ->modalWidth('4xl')
                //             ->modalContent(fn($record) => view(
                //                 'filament.components.file-viewer',
                //                 ['url' => Storage::url($record->file_do ?? '')]
                //             ))
                //     ),
                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('no DO telah disalin')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color(Color::Blue),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cara_bayar')
                    ->label('Cara Bayar')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tunai' => 'success',
                        'transfer' => 'info',
                        'cair di luar' => 'warning',
                        'belum dibayar' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supir.nama')
                    ->label('Supir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->suffix(' Kg')
                    ])
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(Color::Amber)
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR', true)
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('upah_bongkar')
                    ->label('Upah Bongkar')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR', true)
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('biaya_lain')
                    ->label('Biaya')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR', true)
                    ])
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('keterangan_biaya_lain')
                    ->hidden()
                    ->label('Keterangan Biaya Lain'),

                Tables\Columns\TextColumn::make('hutang_awal')
                    ->label('Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(Color::Red)
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembayaran_hutang')
                    ->label('Bayar Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR', true)
                    ])
                    ->color(Color::Orange)
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_hutang_penjual')  // Sesuaikan dengan nama kolom di database
                    ->label('Sisa Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->state(function (TransaksiDo $record): int {
                        // Gunakan field sisa_hutang_penjual yang sudah dihitung
                        return $record->sisa_hutang_penjual ?? max(0, $record->hutang_awal - $record->pembayaran_hutang);
                    })
                    ->sortable()
                    // ->summarize([
                    //     Tables\Columns\Summarizers\Sum::make()
                    //         ->currency('IDR')
                    // ])
                    ->alignRight() // Opsional: untuk alignment nominal uang
                    ->color(
                        fn(TransaksiDo $record): string =>
                        $record->sisa_hutang_penjual > 0 ? 'danger' : 'success'
                    ),

                // Tables\Columns\TextColumn::make('saldo_perusahaan')
                //     ->label('Saldo Perusahaan')
                //     ->formatStateUsing(function () {
                //         // Ambil data saldo terkini
                //         $perusahaan = \App\Models\Perusahaan::first();
                //         return 'Rp ' . number_format($perusahaan->saldo ?? 0, 0, ',', '.');
                //     })
                //     ->alignRight()
                //     ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                //     ->weight('bold')
                //     ->searchable(false),


                Tables\Columns\TextColumn::make('sisa_bayar')
                    ->label('Sisa Bayar')
                    ->formatStateUsing(function (TransaksiDo $record) {
                        // Hitung ulang komponen pengurangan
                        $komponenPengurangan =
                            $record->upah_bongkar +
                            $record->biaya_lain +
                            $record->pembayaran_hutang;

                        // Hitung sisa bayar yang benar
                        $sisaBayar = max(0, $record->sub_total - $komponenPengurangan);

                        return 'Rp ' . number_format($sisaBayar, 0, ',', '.');
                    })
                    ->color(Color::Emerald)
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR', true)
                    ])
                    ->sortable(),

                // Tables\Columns\TextColumn::make('status_bayar')
                //     ->label('Status Bayar')
                //     ->badge()
                //     ->color(fn(string $state): string => match ($state) {
                //         'Lunas' => 'success',
                //         'Belum Lunas' => 'warning',
                //         default => 'gray',
                //     }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->searchable()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->reorderable()
            ->poll('5s')
            ->persistSortInSession()
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->format('d-m-Y')
                            ->native(false)
                            ->maxDate(now())
                            ->placeholder('Dari Tanggal')
                            ->required(),
                        Forms\Components\DatePicker::make('created_to')
                            ->label('Sampai Tanggal')
                            ->maxDate(now())
                            ->native(false)
                            ->placeholder('Sampai Tanggal')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['created_from'])) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }

                        if (!empty($data['created_to'])) {
                            $query->whereDate('created_at', '<=', $data['created_to']);
                        }

                        return $query;
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter Tanggal')
            )
            ->actions([
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (TransaksiDo $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo $record->generatePdf();
                        }, "DO-{$record->nomor}.pdf");
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Print Transaksi DO')
                    ->modalDescription('Apakah Anda yakin ingin mencetak transaksi ini?')
                    ->modalSubmitActionLabel('Ya, Cetak')
                    ->modalIcon('heroicon-o-printer'),
                Tables\Actions\EditAction::make(),
            ])

            ->actionsPosition(ActionsPosition::BeforeColumns)
            // Tambahkan konfigurasi action lainnya jika diperlukan
            ->actionsColumnLabel('Actions') // Optional: untuk memberi label kolom action
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),

                // ]),
            ])
            ->emptyStateHeading('Belum ada data Transaksi DO Hari Ini')
            ->emptyStateDescription('Silakan tambah Transaksi DO baru dengan klik tombol di atas')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->defaultSort('tanggal', 'desc')
            ->poll('60s') // Poll setiap 60 detik
            ->deferLoading() // Menunda loading sampai halaman sepenuhnya dimuat
            ->persistFiltersInSession() // Menyimpan filter di session
            ->persistSortInSession() // Menyimpan pengurutan di session
            ->striped();
    }




    // Helper method untuk update sisa bayar
    private static function updateSisaBayar(Forms\Get $get, Forms\Set $set): void
    {
        $sub_total = $get('sub_total') ?? 0;
        $upahBongkar = $get('upah_bongkar') ?? 0;
        $biayaLain = $get('biaya_lain') ?? 0;
        $pembayaranHutang = $get('pembayaran_hutang') ?? 0;

        $sisaBayar = $sub_total - $upahBongkar - $biayaLain - $pembayaranHutang;
        $set('sisa_bayar', max(0, $sisaBayar));
    }

    //---------------------------------//


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiDos::route('/'),
            'create' => Pages\CreateTransaksiDo::route('/create'),
            'edit' => Pages\EditTransaksiDo::route('/{record}/edit'),
        ];
    }



    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Helper methods untuk kalkulasi
    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }

    //-----------------------------//
    // Helper methods untuk kalkulasi
    private static function hitungTotal($state, Forms\Get $get, Forms\Set $set): void
    {
        // Format values
        $tonase = self::formatCurrency($get('tonase'));
        $hargaSatuan = self::formatCurrency($get('harga_satuan'));

        if ($tonase && $hargaSatuan) {
            // Calculate total
            $sub_total = $tonase * $hargaSatuan;
            $set('sub_total', $sub_total);

            // Recalculate sisa bayar
            $upahBongkar = self::formatCurrency($get('upah_bongkar'));
            $biayaLain = self::formatCurrency($get('biaya_lain'));
            $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));

            // Total - (Upah Bongkar + Biaya Lain + Bayar Hutang)
            $sisaBayar = max(0, $sub_total - $upahBongkar - $biayaLain - $bayarHutang);
            $set('sisa_bayar', $sisaBayar);
        }
    }



    // ---------------------//

    // Perbaikan logika bayar hutang
    private static function hitungPembayaranHutang($state, Forms\Get $get, Forms\Set $set): void
    {
        // Format values
        $hutang = self::formatCurrency($get('hutang_awal'));
        $bayarHutang = self::formatCurrency($state);

        // Validate bayar hutang
        if ($bayarHutang > $hutang) {
            $bayarHutang = $hutang;
            $set('pembayaran_hutang', $hutang);

            // Perbaikan format notifikasi
            Notification::make()
                ->warning()
                ->title('Pembayaran Hutang')
                ->body(sprintf(
                    'Pembayaran disesuaikan menjadi Rp %s sesuai total hutang',
                    number_format($hutang, 0, ',', '.')
                ))
                ->duration(3000)
                ->persistent()
                ->color('warning')
                ->icon('heroicon-o-banknotes')
                ->send();
        }

        // Update sisa hutang
        $sisaHutang = $hutang - $bayarHutang;
        $set('sisa_hutang_penjual', max(0, $sisaHutang));

        // Recalculate sisa bayar
        $sub_total = self::formatCurrency($get('sub_total'));
        $upahBongkar = self::formatCurrency($get('upah_bongkar'));
        $biayaLain = self::formatCurrency($get('biaya_lain'));

        // Sisa Bayar = Total - (Upah Bongkar + Biaya Lain + Bayar Hutang)
        $sisaBayar = $sub_total - $upahBongkar - $biayaLain - $bayarHutang;
        $set('sisa_bayar', max(0, $sisaBayar));
    }


    private static function hitungSisaBayar($state, Forms\Get $get, Forms\Set $set): void
    {
        try {
            // Format values
            $subTotal = self::formatCurrency($get('sub_total'));
            $upahBongkar = self::formatCurrency($get('upah_bongkar'));
            $biayaLain = self::formatCurrency($get('biaya_lain'));
            $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));

            // Hitung komponen pengurangan
            $komponenPengurangan = $upahBongkar + $biayaLain + $bayarHutang;

            // Hitung sisa bayar
            $sisaBayar = max(0, $subTotal - $komponenPengurangan);

            // Set nilai sisa bayar
            $set('sisa_bayar', $sisaBayar);

            // Log perhitungan untuk validasi
            Log::info('Perhitungan Sisa Bayar:', [
                'sub_total' => $subTotal,
                'upah_bongkar' => $upahBongkar,
                'biaya_lain' => $biayaLain,
                'bayar_hutang' => $bayarHutang,
                'komponen_pengurangan' => $komponenPengurangan,
                'sisa_bayar' => $sisaBayar
            ]);
        } catch (\Exception $e) {
            Log::error('Error menghitung sisa bayar:', [
                'error' => $e->getMessage(),
                'state' => $state
            ]);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Format numeric fields
        $numericFields = [
            'tonase',
            'harga_satuan',
            'upah_bongkar',
            'biaya_lain',
            'pembayaran_hutang'
        ];

        foreach ($numericFields as $field) {
            $data[$field] = self::formatCurrency($data[$field] ?? 0);
        }

        // Get fresh hutang from penjual
        if (!empty($data['penjual_id'])) {
            $penjual = Penjual::find($data['penjual_id']);
            if ($penjual) {
                $data['hutang_awal'] = $penjual->hutang_awal;

                // Revalidate pembayaran_hutang
                if ($data['pembayaran_hutang'] > $data['hutang_awal']) {
                    $data['pembayaran_hutang'] = $data['hutang_awal'];
                }
            }
        }

        // Calculate derived values
        $data['sub_total'] = $data['tonase'] * $data['harga_satuan'];
        $data['sisa_hutang_penjual'] = max(0, $data['hutang_awal'] - $data['pembayaran_hutang']);
        $data['sisa_bayar'] = max(0, $data['sub_total'] - $data['upah_bongkar'] - $data['biaya_lain'] - $data['pembayaran_hutang']);

        return $data;
    }

    protected function mutateFormDataBeforeEdit(array $data): array
    {
        try {
            // Pastikan semua komponen perhitungan tersedia
            $subTotal = $data['sub_total'] ?? 0;
            $upahBongkar = $data['upah_bongkar'] ?? 0;
            $biayaLain = $data['biaya_lain'] ?? 0;
            $bayarHutang = $data['pembayaran_hutang'] ?? 0;

            // Hitung ulang sisa bayar
            $komponenPengurangan = $upahBongkar + $biayaLain + $bayarHutang;
            $data['sisa_bayar'] = max(0, $subTotal - $komponenPengurangan);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error mempersiapkan data edit:', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return $data;
        }
    }


    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        try {
            // Format numeric fields
            $numericFields = [
                'tonase',
                'harga_satuan',
                'upah_bongkar',
                'biaya_lain',
                'pembayaran_hutang'
            ];

            foreach ($numericFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = self::formatCurrency($data[$field]);
                }
            }

            // Penting: Ambil data penjual & hutang saat update
            if (!empty($data['penjual_id'])) {
                $penjual = Penjual::find($data['penjual_id']);
                if ($penjual) {
                    // Set hutang awal dari data penjual atau 0 jika null
                    $data['hutang_awal'] = $penjual->hutang ?? 0;

                    // Revalidasi pembayaran hutang
                    if (
                        isset($data['pembayaran_hutang']) &&
                        $data['pembayaran_hutang'] > $data['hutang_awal']
                    ) {
                        $data['pembayaran_hutang'] = $data['hutang_awal'];
                    }
                }
            }

            // Hitung ulang nilai turunan
            if (isset($data['tonase']) && isset($data['harga_satuan'])) {
                $data['sub_total'] = $data['tonase'] * $data['harga_satuan'];
            }

            if (isset($data['hutang_awal']) && isset($data['pembayaran_hutang'])) {
                $data['sisa_hutang_penjual'] = max(0, $data['hutang_awal'] - $data['pembayaran_hutang']);
            }

            if (isset($data['sub_total'])) {
                $komponenPengurangan =
                    ($data['upah_bongkar'] ?? 0) +
                    ($data['biaya_lain'] ?? 0) +
                    ($data['pembayaran_hutang'] ?? 0);

                $data['sisa_bayar'] = max(0, $data['sub_total'] - $komponenPengurangan);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error mutating form data:', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }


    public static function getFormStatePath(): ?string
    {
        return 'data';
    }

    public static function refreshFormData($set): void
    {
        $perusahaan = \App\Models\Perusahaan::first();
        $set('saldo_perusahaan', 'Rp ' . number_format($perusahaan->saldo ?? 0, 0, ',', '.'));
    }
}
