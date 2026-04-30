<?php

namespace App\Filament\Resources\TransaksiDos\Schemas;


use App\Models\Penjual;
use App\Models\TransaksiDo;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class TransaksiDoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('perusahaan_id')
                    ->default(fn() => Auth::user()?->perusahaan_id),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Section::make('Transaksi DO')
                    ->icon('heroicon-m-document-text')
                    ->components([
                        // 1. Tanggal & Nama Penjual
                        Group::make([
                            TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(fn() => TransaksiDo::generateMonthlyNumber())
                                ->required()
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i:s')
                                ->default(Carbon::now())
                                ->required(),

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
                                        ->required()
                                        ->maxLength(255)
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
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
                                            $sisaHutang = (float) $penjual->hutang;
                                            $set('hutang_awal', $sisaHutang);
                                            $set('pembayaran_hutang', null);
                                            self::hitungSisaBayar($get, $set);
                                        }
                                    }
                                }),
                        ])->columns(2),

                        // 2. Nama Supir & Nomor Polisi
                        Group::make([
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
                                        ->required()
                                        ->maxLength(255)
                                        ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
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
                        Group::make([
                            TextInput::make('tonase')
                                ->label('Tonase (Kg)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->suffix('Kg')
                                ->required()
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungTotal($state, $get, $set)),
                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->required()
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungTotal($get('tonase'), $get, $set)),
                            TextInput::make('sub_total')
                                ->label('Sub Total')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated()
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->extraInputAttributes(['class' => 'bg-gray-50 font-bold text-xl']),
                        ])->columns(3),

                        // 5. Pengurangan (Biaya & Hutang)
                        Group::make([
                            TextInput::make('upah_bongkar')
                                ->label('Upah Bongkar')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->placeholder('0')
                                ->default(null)
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),
                            TextInput::make('biaya_lain')
                                ->label('Biaya Lain/Pengambilan')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->placeholder('0')
                                ->default(null)
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),
                            TextInput::make('pembayaran_hutang')
                                ->label('Potong Hutang')
                                ->hint(fn(Get $get) => $get('penjual_id') ? 'Sisa Hutang: Rp ' . number_format($get('hutang_awal') ?? 0, 0, ',', '.') : null)
                                ->hintColor('danger')
                                ->hintIcon('heroicon-m-exclamation-circle')
                                ->helperText(fn(Get $get) => $get('penjual_id') ? 'Hutang penjual saat ini: Rp ' . number_format($get('hutang_awal') ?? 0, 0, ',', '.') : 'Pilih penjual untuk melihat hutang')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->placeholder('0')
                                ->default(null)
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set))
                                ->rules([
                                    fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $val = (float) str_replace(['.', ','], '', $value ?? 0);
                                        $hutang = (float) ($get('hutang_awal') ?? 0);
                                        if ($val > $hutang) {
                                            $fail("Potongan tidak boleh melebihi sisa hutang (Rp " . number_format($hutang, 0, ',', '.') . ")");
                                        }

                                        $subTotal = (float) str_replace(['.', ','], '', $get('sub_total') ?? 0);
                                        $upah = (float) str_replace(['.', ','], '', $get('upah_bongkar') ?? 0);
                                        $lain = (float) str_replace(['.', ','], '', $get('biaya_lain') ?? 0);
                                        $pengurangan = $upah + $lain;
                                        $maxBayar = max(0, $subTotal - $pengurangan);

                                        if ($val > $maxBayar) {
                                            $fail("Potongan tidak boleh melebihi sisa hasil transaksi (Rp " . number_format($maxBayar, 0, ',', '.') . ")");
                                        }
                                    },
                                ]),
                        ])->columns(3),

                        // Ringkasan & Total
                        Group::make([
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
                                            $perusahaan = \Filament\Facades\Filament::getTenant();
                                            if (!$perusahaan) return;

                                            $cekNominal = 0;
                                            if ($value === 'tunai') {
                                                $cekNominal = (int) str_replace(['.', ','], '', $get('sisa_bayar') ?? 0);
                                            } elseif ($value === 'tunai & transfer') {
                                                $cekNominal = (int) str_replace(['.', ','], '', $get('nominal_tunai') ?? 0);
                                            }

                                            if ($cekNominal > 0 && $cekNominal > $perusahaan->saldo) {
                                                $fail("Saldo perusahaan tidak mencukupi (Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . ")");
                                            }
                                        };
                                    },
                                ]),

                            TextInput::make('sisa_bayar')
                                ->label('Total Bayar ke Penjual')
                                ->prefix('Rp')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->readOnly()
                                ->dehydrated()
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->extraAttributes([
                                    'class' => 'bg-blue-50 dark:bg-gray-800 p-3 rounded-xl border border-blue-100 dark:border-gray-700 shadow-sm mb-2',
                                    'style' => 'width: 100%;'
                                ])
                                ->extraInputAttributes([
                                    'style' => 'font-size: 1.25rem !important; font-weight: 800; color: #2563eb !important; -webkit-text-fill-color: #2563eb !important; opacity: 1 !important; background: transparent; border: none; height: auto; line-height: 1.2;',
                                    'class' => 'text-blue-600 dark:text-blue-400'
                                ]),

                            Text::make(fn() => 'Saldo Perusahaan: Rp ' . number_format(\Filament\Facades\Filament::getTenant()->saldo ?? 0, 0, ',', '.'))
                                ->weight('bold')
                                ->extraAttributes([
                                    'style' => 'font-weight: 700; font-size: 1rem; color: #ffffff !important; background-color: #2563eb !important; display: inline-block; padding: 6px 16px; border-radius: 8px; border: 1px solid #1d4ed8; box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1);',
                                    'class' => 'mt-1 mb-4'
                                ]),

                            

                            TextInput::make('nominal_tunai')
                                ->label('Nominal Tunai')
                                ->helperText('Jumlah cash yang diambil')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->placeholder('0')
                                ->default(null)
                                ->required()
                                ->live(onBlur: true)
                                ->debounce(500)
                                ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                                ->visible(fn(Get $get) => $get('cara_bayar') === 'tunai & transfer')
                                ->rules([
                                    function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $sisaBayar = (int) str_replace(['.', ','], '', $get('sisa_bayar') ?? 0);
                                            $val = (int) str_replace(['.', ','], '', $value ?? 0);
                                            if ($val > $sisaBayar) {
                                                $fail("Nominal tunai tidak boleh melebihi total bayar (Rp " . number_format($sisaBayar, 0, ',', '.') . ")");
                                            }
                                        };
                                    },
                                ]),
                        ])->columns(2),

                        // Hidden field untuk referensi hitung
                        TextInput::make('hutang_awal')
                            ->default(0)
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function hitungTotal(mixed $tonase, Get $get, Set $set): void
    {
        $tonaseValue = self::formatCurrency($tonase);
        $hargaSatuan = self::formatCurrency($get('harga_satuan'));
        $subTotal = $tonaseValue * $hargaSatuan;
        
        // Simpan sebagai integer bulat
        $set('sub_total', (int) round($subTotal));
        self::hitungSisaBayar($get, $set);
    }

    public static function hitungSisaBayar(Get $get, Set $set): void
    {
        $subTotal = self::formatCurrency($get('sub_total'));
        $upahBongkar = self::formatCurrency($get('upah_bongkar'));
        $biayaLain = self::formatCurrency($get('biaya_lain'));
        $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));
        
        $sisaBayar = $subTotal - ($upahBongkar + $biayaLain + $bayarHutang);
        $set('sisa_bayar', max(0, (int) round($sisaBayar)));
    }

    private static function formatCurrency(mixed $number): float
    {
        if (empty($number)) return 0;
        
        // Jika sudah numeric (int/float) dan bukan string numerik
        if (is_numeric($number) && !is_string($number)) {
            return (float) $number;
        }

        $str = (string) $number;
        
        // Hapus spasi jika ada
        $str = str_replace(' ', '', $str);

        // Jika mengandung koma, berarti format Indonesia (1.234,56)
        if (str_contains($str, ',')) {
            // Hapus titik ribuan, ganti koma desimal jadi titik
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } 
        // Jika HANYA mengandung titik
        elseif (str_contains($str, '.')) {
            // Karena kita menggunakan precision 0 (integer) di hampir semua field,
            // maka titik dalam input string "29.000" kemungkinan besar adalah ribuan.
            // Kita hapus titiknya agar menjadi 29000, bukan 29.
            $str = str_replace('.', '', $str);
        }
        
        return (float) $str;
    }
}
