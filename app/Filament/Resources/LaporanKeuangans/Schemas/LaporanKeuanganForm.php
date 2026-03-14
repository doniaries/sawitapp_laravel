<?php

namespace App\Filament\Resources\LaporanKeuangans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class LaporanKeuanganForm
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
                                    ->numeric(),
                                TextInput::make('kategori')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('sub_kategori')
                                    ->maxLength(50),
                                TextInput::make('sumber_transaksi')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('referensi_id')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('nomor_referensi')
                                    ->maxLength(50),
                                Textarea::make('keterangan')
                                    ->columnSpanFull(),
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
                                    ->required(),
                                TextInput::make('pihak_terkait')
                                    ->maxLength(100),
                                TextInput::make('tipe_pihak'),
                                TextInput::make('cara_pembayaran')
                                    ->maxLength(20),
                            ]),
                    ]),
            ]);
    }
}
