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
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->disabled(),
                                TextInput::make('kategori')
                                    ->disabled(),
                                TextInput::make('sub_kategori')
                                    ->disabled(),
                                TextInput::make('sumber_transaksi')
                                    ->disabled(),
                                TextInput::make('referensi_id')
                                    ->numeric()
                                    ->disabled(),
                                TextInput::make('nomor_referensi')
                                    ->disabled(),
                                Textarea::make('keterangan')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Metadata')
                            ->description('Konteks tambahan transaksi')
                            ->components([
                                DateTimePicker::make('tanggal')
                                    ->disabled(),
                                TextInput::make('jenis_transaksi')
                                    ->disabled(),
                                TextInput::make('pihak_terkait')
                                    ->disabled(),
                                TextInput::make('tipe_pihak')
                                    ->disabled(),
                                TextInput::make('cara_pembayaran')
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }
}
