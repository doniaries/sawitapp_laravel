<?php

namespace App\Filament\Resources\TransaksiDos\Schemas;

use App\Models\Kendaraan;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\TransaksiDo;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\DB;

class TransaksiDoForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            // Header Section
            Section::make()
                ->components([
                    Grid::make()
                        ->components([
                            Forms\Components\TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(fn() => TransaksiDo::generateMonthlyNumber())
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i:s')
                                ->default(Carbon::now())
                                ->required()
                                ->dehydrated(),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),

            // Detail Pengiriman Section
            Grid::make()
                ->components([
                    Section::make()
                        ->components([
                            Grid::make()
                                ->components([
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
                                            Forms\Components\TextInput::make('hutang')
                                                ->label(fn($context) => $context === 'create' ? 'Hutang Awal' : 'Total Hutang')
                                                ->dehydrated()
                                                ->prefix('Rp')
                                                ->numeric()
                                                ->default(0)
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0),
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

                                    Forms\Components\Select::make('supir_id')
                                        ->label('Supir')
                                        ->relationship('supir', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(fn($state, Set $set) => $set('kendaraan_id', null))
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('nama')->required()->maxLength(255),
                                            Forms\Components\TextInput::make('alamat')->maxLength(255),
                                            Forms\Components\TextInput::make('telepon')->tel()->maxLength(255),
                                        ]),

                                    Forms\Components\Select::make('kendaraan_id')
                                        ->label('Nomor Polisi')
                                        ->options(function (Get $get) {
                                            $supirId = $get('supir_id');
                                            return $supirId ? Kendaraan::query()->where('supir_id', $supirId)->pluck('no_polisi', 'id') : [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('no_polisi')->required()->unique(Kendaraan::class, 'no_polisi')->maxLength(10),
                                            Forms\Components\Hidden::make('supir_id')->default(fn(Get $get) => $get('../../supir_id'))
                                        ])
                                        ->createOptionUsing(function (array $data, Get $get) {
                                            DB::beginTransaction();
                                            try {
                                                $noPolisi = strtoupper(trim($data['no_polisi']));
                                                $supirId = $data['supir_id'] ?? $get('supir_id');
                                                $kendaraan = Kendaraan::create(['no_polisi' => $noPolisi, 'supir_id' => $supirId]);
                                                DB::commit();
                                                return $kendaraan->id;
                                            } catch (\Exception $e) {
                                                DB::rollBack();
                                                throw $e;
                                            }
                                        }),

                                    Forms\Components\TextInput::make('tonase')
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

                                    Forms\Components\TextInput::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->required()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungTotal($get('tonase'), $get, $set)),
                                ])
                                ->columns(3),
                        ])
                        ->columnSpan(2),

                    Section::make()
                        ->components([
                            Forms\Components\TextInput::make('hutang_awal')
                                ->label('Total Hutang')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated()
                                ->numeric()
                                ->default(0),
                            Forms\Components\TextInput::make('sub_total')
                                ->label('Sub Total')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Perhitungan & Pembayaran Section
            Grid::make()
                ->components([
                    Section::make()
                        ->components([
                            Grid::make()
                                ->components([
                                    Forms\Components\TextInput::make('upah_bongkar')
                                        ->label('Upah Bongkar')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),

                                    Forms\Components\TextInput::make('biaya_lain')
                                        ->label('Biaya')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Get $get, Set $set) => self::hitungSisaBayar($get, $set)),

                                    Forms\Components\TextInput::make('pembayaran_hutang')
                                        ->label('Bayar Hutang')
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

                                    Forms\Components\Select::make('cara_bayar')
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

                                    Section::make('Informasi Saldo')
                                        ->components([
                                            Forms\Components\Placeholder::make('saldo_perusahaan')
                                                ->label('Saldo Perusahaan')
                                                ->content(fn() => 'Rp ' . number_format(\App\Models\Perusahaan::first()->saldo ?? 0, 0, ',', '.'))
                                                ->extraAttributes(['class' => 'text-lg font-semibold']),
                                        ]),
                                ])
                                ->columns(3),
                        ])
                        ->columnSpan(2),

                    Section::make()
                        ->components([
                            Forms\Components\TextInput::make('sisa_hutang_penjual')
                                ->label('Sisa Hutang')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
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
                                ->live(),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),
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
