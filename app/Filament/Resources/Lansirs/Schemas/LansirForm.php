<?php

namespace App\Filament\Resources\Lansirs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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
                            ->numeric()
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $tonase = (float) $state;
                                $harga = (float) $get('harga_satuan');
                                
                                // Total = Tonase * Harga Satuan
                                $set('total', $tonase * $harga);
                                
                                // Upah = Tonase (Kg) * 100 (sama dengan 100.000 per Ton)
                                $set('upah', $tonase * 100);
                            }),
                        TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $harga = (float) $state;
                                $tonase = (float) $get('tonase');
                                
                                $set('total', $tonase * $harga);
                            }),
                        TextInput::make('total')
                            ->label('Total Pembayaran')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp')
                            ->readOnly()
                            ->extraAttributes(['class' => 'bg-gray-100 font-bold']),
                        TextInput::make('upah')
                            ->label('Upah Supir (Rp 100k/Ton)')
                            ->required()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('Rp')
                            ->readOnly()
                            ->extraAttributes(['class' => 'bg-blue-50 font-bold']),
                    ]),
            ]);
    }
}
