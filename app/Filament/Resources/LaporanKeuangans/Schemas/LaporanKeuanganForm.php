<?php

namespace App\Filament\Resources\LaporanKeuangans\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class LaporanKeuanganForm
{
    public static function configure(\Filament\Forms\Form $schema): \Filament\Forms\Form
    {
        return $schema->schema([
            Forms\Components\DateTimePicker::make('tanggal')
                ->required(),
            Forms\Components\TextInput::make('jenis_transaksi')
                ->required(),
            Forms\Components\TextInput::make('kategori')
                ->required()
                ->maxLength(50),
            Forms\Components\TextInput::make('sub_kategori')
                ->maxLength(50),
            Forms\Components\TextInput::make('nominal')
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('sumber_transaksi')
                ->required()
                ->maxLength(50),
            Forms\Components\TextInput::make('referensi_id')
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('nomor_referensi')
                ->maxLength(50),
            Forms\Components\TextInput::make('pihak_terkait')
                ->maxLength(100),
            Forms\Components\TextInput::make('tipe_pihak'),
            Forms\Components\TextInput::make('cara_pembayaran')
                ->maxLength(20),
            Forms\Components\Textarea::make('keterangan')
                ->columnSpanFull(),
        ]);
    }
}
