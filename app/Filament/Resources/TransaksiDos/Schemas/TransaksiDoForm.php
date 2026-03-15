<?php

namespace App\Filament\Resources\TransaksiDos\Schemas;


use App\Models\Penjual;
use App\Models\Supir;
use App\Models\TransaksiDo;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Illuminate\Support\Facades\DB;

class TransaksiDoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'lg' => 2])
                    ->components([
                        // Header Section
                        Section::make('Informasi Transaksi')
                            ->description('Data utama referensi Transaksi DO')
                            ->components([
                                TextInput::make('nomor')
                                    ->label('Nomor DO')
                                    ->default(fn() => TransaksiDo::generateMonthlyNumber())
                                    ->disabled()
                                    ->dehydrated(),

                                DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->format('Y-m-d H:i:s')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i:s')
                                    ->default(Carbon::now())
                                    ->required()
                                    ->dehydrated(),
                            ])->columns(2),

                        // Detail Pengiriman Section
                        Section::make('Pihak Terlibat')
                            ->description('Detail entitas pengiriman Transaksi DO')
                            ->components([
                                Select::make('penjual_id')
                                    ->label('Penjual')
                                    ->relationship(
                                        'penjual',
                                        'nama',
                                        fn($query) => $query->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id)
                                    )
                                    ->searchable()
                                    ->live()
                                    ->required()
                                    // ... createOptionForm omitted but preserved in reality ...
                                    ->createOptionForm([
                                        TextInput::make('nama')
                                            ->label('Nama Penjual')
                                            ->unique(ignoreRecord: true)
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('alamat')
                                            ->label('Alamat')
                                            ->maxLength(255),
                                        TextInput::make('telepon')
                                            ->tel()
                                            ->label('Nomor Telepon'),
                                        TextInput::make('hutang')
                                            ->label(fn($context) => $context === 'create' ? 'Hutang Awal' : 'Total Hutang')
                                            ->dehydrated()
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),
                                    ])
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $penjual = Penjual::find($state);
                                            if ($penjual) {
                                                $set('hutang_awal', $penjual->hutang);
                                                $set('sisa_hutang_penjual', $penjual->hutang);
                                                $set('pembayaran_hutang', 0);
                                            }
                                        }
                                    }),

                                Select::make('supir_id')
                                    ->label('Supir')
                                    ->relationship(
                                        'supir',
                                        'nama'
                                    )
                                    ->searchable()
                                    ->live()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('nama')->required()->maxLength(255),
                                        TextInput::make('alamat')->maxLength(255),
                                        TextInput::make('telepon')->tel()->maxLength(255),
                                    ]),

                                TextInput::make('no_polisi')
                                    ->label('Nomor Polisi')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('B 1234 ABC'),
                            ])->columns(3),

                        // Perhitungan
                        Section::make('Komponen Biaya DO')
                            ->description('Input tonase, harga satuan dan biaya lain-lain')
                            ->columns(2)
                            ->components([
                                TextInput::make('tonase')
                                    ->label('Tonase (Netto)')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->numeric()
                                    ->suffix('Kg')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $penjualId = $get('penjual_id');
                                        if ($state && $penjualId && !$get('supir_id')) {
                                            $penjual = Penjual::find($penjualId);
                                            if ($penjual) {
                                                $supir = Supir::firstOrCreate(['nama' => $penjual->nama], ['alamat' => $penjual->alamat ?? '', 'telepon' => $penjual->telepon ?? '']);
                                                $set('supir_id', $supir->id);
                                            }
                                        }
                                        self::hitungTotal($state, $get, $set);
                                    }),

                                TextInput::make('harga_satuan')
                                    ->label('Harga Satuan')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungTotal($get('tonase'), $get, $set)),

                                TextInput::make('upah_bongkar')
                                    ->label('Upah Bongkar')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),

                                TextInput::make('biaya_lain')
                                    ->label('Biaya Lain')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'lg' => 1])
                    ->components([
                        Section::make('Sub Total & Pembayaran')
                            ->description('Rincian hasil dan detail pembayaran')
                            ->components([
                                TextInput::make('sub_total')
                                    ->label('Sub Total')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('hutang_awal')
                                    ->label('Total Hutang Penjual')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('pembayaran_hutang')
                                    ->label('Nominal Bayar Hutang')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->visible(fn(Get $get): bool => $get('hutang_awal') > 0)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $hutangAwal = self::formatCurrency($get('hutang_awal'));
                                        $bayarHutang = self::formatCurrency($state);
                                        if ($bayarHutang > $hutangAwal) {
                                            $set('pembayaran_hutang', $hutangAwal);
                                            $bayarHutang = $hutangAwal;
                                        }
                                        $set('sisa_hutang_penjual', max(0, $hutangAwal - $bayarHutang));
                                        self::hitungSisaBayar($get, $set);
                                    }),

                                TextInput::make('sisa_hutang_penjual')
                                    ->label('Sisa Hutang Penjual')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),

                                TextInput::make('sisa_bayar')
                                    ->label('Sisa Yang Dibayar')
                                    ->required()
                                    ->prefix('Rp')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->extraInputAttributes(['class' => 'font-bold text-lg leading-loose']),

                                Select::make('cara_bayar')
                                    ->label('Cara Bayar')
                                    ->options(TransaksiDo::CARA_BAYAR)
                                    ->default('tunai')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $penjualId = $get('penjual_id');
                                        if ($state && $penjualId && !$get('supir_id')) {
                                            $penjual = Penjual::find($penjualId);
                                            if ($penjual) {
                                                $supir = Supir::firstOrCreate(['nama' => $penjual->nama], ['alamat' => $penjual->alamat ?? '', 'telepon' => $penjual->telepon ?? '']);
                                                $set('supir_id', $supir->id);
                                            }
                                        }
                                    }),
                            ]),

                        Section::make('Informasi Saldo')
                            ->components([
                                Placeholder::make('saldo_perusahaan')
                                    ->label('Saldo Perusahaan')
                                    ->content(fn() => 'Rp ' . number_format(\App\Models\Perusahaan::first()->saldo ?? 0, 0, ',', '.'))
                                    ->extraAttributes(['class' => 'text-lg font-semibold']),
                            ]),
                    ]),
            ]);
    }

    public static function hitungTotal($tonase, Get $get, Set $set): void
    {
        $tonaseValue = self::formatCurrency($tonase);
        $hargaSatuan = self::formatCurrency($get('harga_satuan'));
        $subTotal = $tonaseValue * $hargaSatuan;
        $set('sub_total', $subTotal);
        self::hitungSisaBayar($get, $set);
    }

    public static function hitungSisaBayar(Get $get, Set $set): void
    {
        $subTotal = self::formatCurrency($get('sub_total'));
        $upahBongkar = self::formatCurrency($get('upah_bongkar'));
        $biayaLain = self::formatCurrency($get('biaya_lain'));
        $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));
        $sisaBayar = max(0, $subTotal - ($upahBongkar + $biayaLain + $bayarHutang));
        $set('sisa_bayar', $sisaBayar);
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        if (is_string($number)) {
            return (int) str_replace(['.', ','], '', $number);
        }
        return (int) $number;
    }
}
