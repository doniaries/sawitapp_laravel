<?php

namespace App\Filament\Resources\TransaksiDos\Schemas;


use App\Models\Penjual;
use App\Models\TransaksiDo;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Traits\HasCurrencyInput;

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
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i:s')
                                ->default(Carbon::now())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $set('nomor', TransaksiDo::generateMonthlyNumber($state));
                                    }
                                })
                                ->rules([
                                    fn($get) => function (string $attribute, $value, $fail) use ($get) {
                                        $perusahaanId = \Filament\Facades\Filament::getTenant()->id;
                                        if (!\App\Models\TutupHari::canModify($value, $perusahaanId)) {
                                            $fail("Data tidak dapat ditambah/diubah karena hari tersebut sudah ditutup.");
                                        }
                                    },
                                ]),
                            TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(fn() => TransaksiDo::generateMonthlyNumber())
                                ->required()
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated(),

                            Select::make('penjual_id')
                                ->label('Nama Penjual')
                                ->relationship(
                                    'penjual',
                                    'nama',
                                    fn($query) => $query->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id)
                                )
                                ->searchable()
                                ->preload()
                                ->live()
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
                                ])
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
                        ])->columns(2),

                        // 2. Nama Supir & Nomor Polisi
                        \Filament\Schemas\Components\Group::make([
                            Select::make('supir_id')
                                ->label('Nama Supir')
                                ->relationship(
                                    'supir',
                                    'nama',
                                    fn($query) => $query->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id)
                                )
                                ->searchable()
                                ->preload()
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
                                ]),

                            TextInput::make('no_polisi')
                                ->label('Nomor Polisi')
                                ->placeholder('B 1234 ABC')
                                ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                        ])->columns(2),


                        // 4. Tonase & Harga -> Sub Total
                        \Filament\Schemas\Components\Group::make([
                            self::numericInput(
                                TextInput::make('tonase'),
                                precision: 0,
                                suffix: 'Kg'
                            )
                                ->label('Tonase (Kg)')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),
                            self::currencyInput(
                                TextInput::make('harga_satuan')
                            )
                                ->label('Harga Satuan')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),
                            self::currencyInput(
                                TextInput::make('sub_total')
                            )
                                ->label('Sub Total')
                                ->disabled()
                                ->dehydrated()
                                ->extraInputAttributes(['class' => 'bg-gray-50 font-bold text-xl']),
                        ])->columns(3),

                        // 5. Pengurangan (Biaya & Hutang)
                        \Filament\Schemas\Components\Group::make([
                            self::currencyInput(
                                TextInput::make('upah_bongkar')
                            )
                                ->label('Upah Bongkar')
                                ->placeholder('0')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),
                            self::currencyInput(
                                TextInput::make('biaya_lain')
                            )
                                ->label('Biaya Lain/Pengambilan')
                                ->placeholder('0')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set)),
                            self::currencyInput(
                                TextInput::make('pembayaran_hutang')
                            )
                                ->label('Potong Hutang')
                                ->hint(fn(Get $get) => $get('penjual_id') ? 'Sisa Hutang: ' . money($get('hutang_awal') ?? 0, 'IDR') : null)
                                ->hintColor('danger')
                                ->hintIcon('heroicon-m-exclamation-circle')
                                ->helperText(fn(Get $get) => $get('penjual_id') ? 'Hutang penjual saat ini: ' . money($get('hutang_awal') ?? 0, 'IDR') : 'Pilih penjual untuk melihat hutang')
                                ->placeholder('0')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::applyCalculations($get, $set))
                                ->rules([
                                    fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $error = TransaksiDo::validatePotonganHutang(
                                            $value,
                                            $get('hutang_awal'),
                                            $get('sub_total'),
                                            $get('upah_bongkar'),
                                            $get('biaya_lain')
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

                            self::currencyInput(
                                TextInput::make('sisa_bayar')
                            )
                                ->label('Total Bayar ke Penjual')
                                ->readOnly()
                                ->dehydrated()
                                ->extraAttributes([
                                    'class' => 'bg-blue-50 dark:bg-gray-800 p-3 rounded-xl border border-blue-100 dark:border-gray-700 shadow-sm mb-2',
                                    'style' => 'width: 100%;'
                                ])
                                ->extraInputAttributes([
                                    'style' => 'font-size: 1.25rem !important; font-weight: 800; color: #2563eb !important; -webkit-text-fill-color: #2563eb !important; opacity: 1 !important; background: transparent; border: none; height: auto; line-height: 1.2;',
                                    'class' => 'text-blue-600 dark:text-blue-400'
                                ]),

                            Text::make(fn() => 'Saldo Perusahaan: ' . money(\Filament\Facades\Filament::getTenant()->saldo ?? 0, 'IDR'))
                                ->weight('bold')
                                ->extraAttributes([
                                    'style' => 'font-weight: 700; font-size: 1rem; color: #010d10 !important; background-color: #e6ca28 !important; display: inline-block; padding: 6px 16px; border-radius: 8px; box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1);',
                                    'class' => 'mt-1 mb-4'
                                ]),



                            self::currencyInput(
                                TextInput::make('nominal_tunai')
                            )
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

                        \Filament\Schemas\Components\Section::make('Validasi & Lampiran')
                            ->description('Penanda kecocokan data dan unggah bukti rekap')
                            ->collapsible()
                            ->components([
                                Toggle::make('is_mismatch')
                                    ->label('Hitungan Sistem Tidak Cocok')
                                    ->helperText('Tandai jika data pembukuan tidak sesuai dengan hitungan sistem')
                                    ->onColor('danger')
                                    ->onIcon('heroicon-m-exclamation-triangle')
                                    ->offIcon('heroicon-m-check-circle'),

                                FileUpload::make('bukti_rekap')
                                    ->label('Unggah Bukti Pedoman Rekap Kasir')
                                    ->disk('public')
                                    ->directory('bukti-rekap')
                                    ->image()
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Unggah foto/scan bukti rekap dari kasir'),
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
