<?php

namespace App\Filament\Resources\JurnalKeuangans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class JurnalKeuanganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Transaksi')
                            ->description('Detail riwayat transaksi keuangan')
                            ->components([
                                TextInput::make('nominal')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->debounce(500),
                                TextInput::make('kategori')
                                    ->required()
                                    ->maxLength(50)
                                    ->debounce(500),
                                TextInput::make('sub_kategori')
                                    ->maxLength(50)
                                    ->debounce(500),
                                TextInput::make('sumber_transaksi')
                                    ->required()
                                    ->maxLength(50)
                                    ->debounce(500),
                                TextInput::make('referensi_id')
                                    ->required()
                                    ->numeric()
                                    ->debounce(500),
                                TextInput::make('nomor_referensi')
                                    ->maxLength(50)
                                    ->debounce(500),
                                Textarea::make('keterangan')
                                    ->columnSpanFull()
                                    ->debounce(500),
                            ])->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Metadata')
                            ->description('Konteks tambahan transaksi')
                            ->components([
                                DateTimePicker::make('tanggal')
                                    ->required(),
                                TextInput::make('jenis_transaksi')
                                    ->required()
                                    ->debounce(500),
                                TextInput::make('pihak_terkait')
                                    ->maxLength(100)
                                    ->debounce(500),
                                TextInput::make('tipe_pihak')
                                    ->debounce(500),
                                TextInput::make('cara_pembayaran')
                                    ->maxLength(20)
                                    ->debounce(500),
                            ]),
                    ]),
            ]);
    }
}
