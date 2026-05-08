<?php

namespace App\Filament\Resources\TransaksiDos\Schemas;


use App\Models\Penjual;
use App\Models\TransaksiDo;
use App\Traits\HasCurrencyInput;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransaksiDoForm
{
    use HasCurrencyInput;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('perusahaan_id')
                    ->default(fn() => Auth::user()?->perusahaan_id),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                \Filament\Schemas\Components\Section::make('Transaksi DO')
                    ->icon('heroicon-m-document-text')
                    ->components([
                        // 1. Tanggal & Nama Penjual
                        \Filament\Schemas\Components\Group::make([

                            TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(fn() => TransaksiDo::generateMonthlyNumber())
                                ->required()
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated(),
                        ])->columns(2),

                        \Filament\Schemas\Components\Group::make([
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->autofocus()
                                ->displayFormat('d M Y')
                                ->default(Carbon::now())
                                ->required()
                                ->live()
                                ->readOnly(fn($record) => $record && !\Illuminate\Support\Facades\Auth::user()->isSuperAdmin()) // Kunci tanggal saat edit
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $set('nomor', TransaksiDo::generateMonthlyNumber($state));
                                    }
                                })
                                ->rules([
                                    fn() => function (string $attribute, $value, \Closure $fail) {
                                        // Jika Superadmin, boleh lewat
                                        if (\Illuminate\Support\Facades\Auth::user()->isSuperAdmin()) return;

                                        $perusahaanId = \Filament\Facades\Filament::getTenant()->id;
                                        $tanggal = \Carbon\Carbon::parse($value)->toDateString();

                                        $isClosed = \App\Models\TutupHari::where('perusahaan_id', '=', $perusahaanId, 'and')
                                            ->where('tanggal', '=', $tanggal, 'and')
                                            ->where('status', '=', 'closed', 'and')
                                            ->exists();

                                        if ($isClosed) {
                                            $fail('Tanggal ini sudah ditutup. Hanya Superadmin yang dapat menambah atau mengubah data pada tanggal ini.');
                                        }
                                    },
                                ]),
                            Select::make('penjual_id')
                                ->label('Nama Penjual')
                                ->helperText(fn(Get $get) => $get('penjual_id')
                                    ? 'Hutang saat ini: ' . money($get('hutang_awal') ?? 0, 'IDR')
                                    : null)
                                ->relationship(
                                    'penjual',
                                    'nama',
                                    fn($query) => $query->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id)
                                )
                                ->searchable()
                                ->searchDebounce(500)
                                ->preload() // Aktifkan kembali agar nama muncul saat diklik
                                ->optionsLimit(50) // Batasi 50 data awal agar browser tidak berat
                                ->live()
                                ->suffixIcon('heroicon-m-magnifying-glass')
                                ->required()
                                ->noOptionsMessage('Data penjual belum ada')
                                ->searchingMessage('Mencari penjual...')
                                ->loadingMessage('Memuat data...')
                                ->placeholder('Pilih Penjual')
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->label('Nama')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(Penjual::class, 'nama', modifyRuleUsing: function ($rule) {
                                            return $rule->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id);
                                        })
                                        ->validationMessages([
                                            'unique' => ':attribute sudah terdaftar di sistem.',
                                        ])
                                        ->validationAttribute('Nama Penjual')
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                        ->debounce(500)
                                        ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                                    TextInput::make('alamat')
                                        ->maxLength(255)
                                        ->debounce(500),
                                    TextInput::make('telepon')
                                        ->tel()
                                        ->maxLength(255)
                                        ->debounce(500),
                                    \App\Traits\HasCurrencyInput::currencyInput(
                                        TextInput::make('hutang')
                                            ->label('Sisa Hutang')
                                            ->dehydrated()
                                            ->dehydrateStateUsing(function ($state) {
                                                return \App\Traits\HasCurrencyInput::sanitizeNumber($state);
                                            })
                                    ),
                                ])
                                ->createOptionAction(fn($action) => $action->slideOver())
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $penjual = \App\Models\Penjual::query()->find($state);
                                        if ($penjual) {
                                            $set('hutang_awal', (float) $penjual->hutang);
                                            $set('pembayaran_hutang', 0);
                                            self::applyCalculations($get, $set);
                                        }
                                    }
                                }),

                            Select::make('supir_id')
                                ->label('Nama Supir')
                                ->relationship(
                                    'supir',
                                    'nama',
                                    fn($query) => $query->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id)
                                )
                                ->searchable()
                                ->searchDebounce(500)
                                ->preload() // Aktifkan kembali agar nama muncul saat diklik
                                ->optionsLimit(50) // Batasi 50 data awal agar browser tidak berat
                                ->live()
                                ->suffixIcon('heroicon-m-magnifying-glass')
                                ->helperText(fn(Get $get) => $get('supir_id')
                                    ? 'Hutang saat ini: ' . money($get('hutang_awal_supir') ?? 0, 'IDR')
                                    : null)
                                ->required()
                                ->noOptionsMessage('Data supir belum ada')
                                ->searchingMessage('Mencari supir...')
                                ->loadingMessage('Memuat data...')
                                ->placeholder('Pilih Supir')
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->label('Nama')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(\App\Models\Supir::class, 'nama', modifyRuleUsing: function ($rule) {
                                            return $rule->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id);
                                        })
                                        ->validationMessages([
                                            'unique' => ':attribute sudah terdaftar di sistem.',
                                        ])
                                        ->validationAttribute('Nama Supir')
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                        ->debounce(500)
                                        ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                                    TextInput::make('alamat')
                                        ->maxLength(255)
                                        ->debounce(500),
                                    TextInput::make('telepon')
                                        ->tel()
                                        ->maxLength(255)
                                        ->debounce(500),
                                    \App\Traits\HasCurrencyInput::currencyInput(
                                        TextInput::make('hutang')
                                            ->label('Sisa Hutang')
                                            ->dehydrated()
                                            ->dehydrateStateUsing(function ($state) {
                                                return \App\Traits\HasCurrencyInput::sanitizeNumber($state);
                                            })
                                    ),
                                ])
                                ->createOptionAction(fn($action) => $action->slideOver())
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $supir = \App\Models\Supir::query()->find($state);
                                        if ($supir) {
                                            $set('hutang_awal_supir', (float) $supir->hutang);
                                        }
                                    }
                                }),

                            TextInput::make('no_polisi')
                                ->label('Nomor Polisi')
                                ->placeholder('B 1234 ABC')
                                ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                        ])->columns(4),


                        // 4. Tonase & Harga -> Sub Total
                        \Filament\Schemas\Components\Group::make([
                            self::currencyInput(TextInput::make('harga_satuan'))
                                ->label('Harga Satuan')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),

                            self::currencyInput(TextInput::make('tonase'))
                                ->label('Tonase (Kg)')
                                ->suffix('Kg')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),
                            self::currencyInput(TextInput::make('sub_total'))
                                ->label('Sub Total')
                                ->disabled()
                                ->dehydrated()
                                ->extraInputAttributes(['class' => 'bg-gray-50 font-bold text-xl'])
                                ->extraAttributes([
                                    'class' => 'bg-blue-50 dark:bg-gray-800 p-3 rounded-xl border border-blue-100 dark:border-gray-700 shadow-sm mb-2',
                                    'style' => 'width: 100%;'
                                ])
                                ->extraInputAttributes([
                                    'style' => 'font-size: 1.25rem !important; font-weight: 800; color: #de8209 !important; -webkit-text-fill-color: #de8209 !important; opacity: 1 !important; background: transparent; border: none; height: auto; line-height: 1.2;',
                                    'class' => 'text-blue-600 dark:text-blue-400'
                                ])

                        ])->columns(3),

                        // 5. Pengurangan (Biaya & Hutang)
                        \Filament\Schemas\Components\Group::make([

                            self::currencyInput(TextInput::make('upah_bongkar'))
                                ->label('Upah Bongkar')
                                ->placeholder('0')
                                ->live()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),

                            self::currencyInput(TextInput::make('biaya_lain'))
                                ->label('Biaya Lain/Pengambilan')
                                ->placeholder('0')
                                ->live()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),

                            self::currencyInput(TextInput::make('pembayaran_hutang'))
                                ->label('Potong Hutang')
                                ->hint(function (Get $get) {
                                    $penjual = $get('penjual_id');
                                    if (!$penjual) return null;

                                    $subTotal = \App\Traits\HasCurrencyInput::sanitizeNumber($get('sub_total') ?? 0);
                                    $biaya = \App\Traits\HasCurrencyInput::sanitizeNumber($get('upah_bongkar') ?? 0) + \App\Traits\HasCurrencyInput::sanitizeNumber($get('biaya_lain') ?? 0);
                                    $maxDariTransaksi = max(0, $subTotal - $biaya);

                                    return "Maks. dari hasil: " . money($maxDariTransaksi, 'IDR');
                                })
                                ->helperText(fn(Get $get) => $get('penjual_id') ? 'Hutang Penjual: ' . money($get('sisa_hutang_penjual') ?? 0, 'IDR') : null)
                                ->hintColor(function (Get $get) {
                                    $val = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($get('pembayaran_hutang') ?? 0);
                                    $subTotal = \App\Traits\HasCurrencyInput::sanitizeNumber($get('sub_total') ?? 0);
                                    $biaya = \App\Traits\HasCurrencyInput::sanitizeNumber($get('upah_bongkar') ?? 0) + \App\Traits\HasCurrencyInput::sanitizeNumber($get('biaya_lain') ?? 0);
                                    $max = $subTotal - $biaya;

                                    return $val > $max ? 'danger' : 'info';
                                })
                                ->hintIcon('heroicon-m-information-circle')
                                ->placeholder('0')
                                ->live() // Hapus onBlur agar instan
                                ->afterStateUpdated(function (Get $get, Set $set, $component) {
                                    self::applyCalculations($get, $set);
                                })
                                ->rules([
                                    fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $subTotal = \App\Traits\HasCurrencyInput::sanitizeNumber($get('sub_total') ?? 0);
                                        $upahBongkar = \App\Traits\HasCurrencyInput::sanitizeNumber($get('upah_bongkar') ?? 0);
                                        $biayaLain = \App\Traits\HasCurrencyInput::sanitizeNumber($get('biaya_lain') ?? 0);
                                        $hutangAwal = \App\Traits\HasCurrencyInput::sanitizeNumber($get('hutang_awal') ?? 0);

                                        $error = TransaksiDo::validatePotonganHutang(
                                            $value,
                                            $hutangAwal,
                                            $subTotal,
                                            $upahBongkar,
                                            $biayaLain
                                        );

                                        if ($error) $fail($error);
                                    },
                                ]),
                        ])->columns(3),

                        // Ringkasan & Total
                        \Filament\Schemas\Components\Group::make([
                            Select::make('cara_bayar')
                                ->label('Cara Bayar')
                                ->options(TransaksiDo::CARA_BAYAR)
                                ->default('tunai')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    if ($state !== 'tunai & transfer') {
                                        $set('nominal_tunai', 0);
                                    }
                                })
                                ->rules([
                                    function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $error = TransaksiDo::validateCaraBayar(
                                                $value,
                                                $get('sisa_bayar'),
                                                $get('nominal_tunai'),
                                                \Filament\Facades\Filament::getTenant()->saldo ?? 0,
                                                Auth::user()
                                            );
                                            if ($error) $fail($error);
                                        };
                                    },
                                ]),

                            self::currencyInput(TextInput::make('sisa_bayar'))
                                ->label('Total Bayar ke Penjual')
                                ->readOnly()
                                ->live()
                                ->dehydrated()
                                ->validationMessages([
                                    'min' => 'Total bayar tidak boleh minus. Silakan kurangi nominal Potong Hutang atau biaya lainnya.',
                                ])
                                ->rules(['numeric', 'min:0'])
                                ->extraAttributes([
                                    'class' => 'bg-blue-50 dark:bg-gray-800 p-3 rounded-xl border border-blue-100 dark:border-gray-700 shadow-sm mb-2',
                                    'style' => 'width: 100%;'
                                ])
                                ->extraInputAttributes(function (Get $get) {
                                    $sisa = (float) \App\Traits\HasCurrencyInput::sanitizeNumber($get('sisa_bayar') ?? 0);
                                    $color = $sisa < 0 ? '#dc2626' : '#17b035'; // Merah jika minus, Hijau jika positif

                                    return [
                                        'style' => "font-size: 1.5rem !important; font-weight: 900; color: {$color} !important; -webkit-text-fill-color: {$color} !important; opacity: 1 !important; background: transparent; border: none; height: auto; line-height: 1.2;",
                                        'class' => 'text-center'
                                    ];
                                }),

                            Text::make(function () {
                                $saldo = \App\Models\Perusahaan::find(\Filament\Facades\Filament::getTenant()->id)->saldo ?? 0;
                                return 'Saldo Kas Saat Ini: ' . money($saldo, 'IDR');
                            })
                                ->weight('bold')
                                ->extraAttributes(function () {
                                    $saldo = \App\Models\Perusahaan::find(\Filament\Facades\Filament::getTenant()->id)->saldo ?? 0;
                                    $bgColor = $saldo < 0 ? '#dc2626' : '#e6ca28';
                                    $textColor = $saldo < 0 ? '#ffffff' : '#010d10';
                                    
                                    return [
                                        'style' => "font-weight: 700; font-size: 1rem; color: {$textColor} !important; background-color: {$bgColor} !important; display: inline-block; padding: 6px 16px; border-radius: 8px; box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1); transition: all 0.3s ease;",
                                        'class' => 'mt-1 mb-4'
                                    ];
                                }),



                            self::currencyInput(TextInput::make('nominal_tunai'))
                                ->label('Nominal Tunai')
                                ->helperText('Jumlah cash yang diambil')
                                ->placeholder('0')
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->visible(fn(Get $get) => $get('cara_bayar') === 'tunai & transfer')
                                ->rules([
                                    function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $error = TransaksiDo::validateNominalTunai($value, $get('sisa_bayar'));
                                            if ($error) $fail($error);
                                        };
                                    },
                                ]),
                        ])->columns(2),



                        // Hidden field untuk referensi hitung
                        TextInput::make('hutang_awal')
                            ->default(0)
                            ->hidden()
                            ->dehydrated(),
                        TextInput::make('sisa_hutang_penjual')
                            ->default(0)
                            ->hidden()
                            ->dehydrated(),
                        TextInput::make('hutang_awal_supir')
                            ->default(0)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }

    /**
     * Memanggil logika perhitungan dari model dan memperbarui state form.
     * Dibuat private agar tidak bisa diakses dari luar sesuai best practice.
     * Letaknya tetap di Form/Schema (Resource) karena berinteraksi langsung dengan Set/Get Filament.
     */
    private static function applyCalculations(Get $get, Set $set): void
    {
        $results = TransaksiDo::updateCalculations([
            'tonase' => $get('tonase'),
            'harga_satuan' => $get('harga_satuan'),
            'upah_bongkar' => $get('upah_bongkar'),
            'biaya_lain' => $get('biaya_lain'),
            'pembayaran_hutang' => $get('pembayaran_hutang'),
            'hutang_awal' => $get('hutang_awal'),
        ]);

        $set('sub_total', $results['sub_total']);
        $set('sisa_bayar', $results['sisa_bayar']);
        $set('sisa_hutang_penjual', $results['sisa_hutang_penjual']);
    }
}
