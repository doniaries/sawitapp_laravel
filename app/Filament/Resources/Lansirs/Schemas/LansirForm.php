<?php

namespace App\Filament\Resources\Lansirs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LansirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informasi Lansir')
                    ->description('Data pengambilan buah sawit langsung ke kebun')
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Grid::make(2)
                            ->components([
                                DatePicker::make('tanggal_lansir')
                                    ->label('Tanggal Lansir')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('nama_supir')
                                    ->label('Nama Supir')
                                    ->required()
                                    ->placeholder('Masukkan nama supir')
                                    ->maxLength(255),
                                TextInput::make('nama_penjual')
                                    ->label('Nama Penjual')
                                    ->required()
                                    ->placeholder('Masukkan nama penjual')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Rincian Biaya')
                    ->description('Kalkulasi tonase dan harga')
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        TextInput::make('tonase')
                            ->label('Tonase (Kg)')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->suffix('Kg')
                            ->live(onBlur: true)
                            ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $tonase = self::formatCurrency($state);
                                $harga = self::formatCurrency($get('harga_satuan'));
                                
                                // Total = Tonase * Harga Satuan
                                $set('total', (int) round($tonase * $harga));
                                
                                // Upah = Tonase (Kg) * 100 (sama dengan 100.000 per Ton)
                                $set('upah', (int) round($tonase * 100));
                            }),
                        TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $harga = self::formatCurrency($state);
                                $tonase = self::formatCurrency($get('tonase'));
                                
                                $set('total', (int) round($tonase * $harga));
                            }),
                        TextInput::make('total')
                            ->label('Total Pembayaran')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated()
                            ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                            ->extraAttributes(['class' => 'bg-gray-100 font-bold']),
                        TextInput::make('upah')
                            ->label('Upah Supir (Rp 100k/Ton)')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated()
                            ->dehydrateStateUsing(fn($state) => self::formatCurrency($state))
                            ->extraAttributes(['class' => 'bg-blue-50 font-bold']),
                    ]),
            ]);
    }

    private static function formatCurrency(mixed $number): float
    {
        if (empty($number)) return 0;
        
        if (is_numeric($number) && !is_string($number)) {
            return (float) $number;
        }

        $str = (string) $number;
        $str = str_replace(' ', '', $str);

        if (str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, '.')) {
            $str = str_replace('.', '', $str);
        }
        
        return (float) $str;
    }
}
