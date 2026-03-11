<?php

namespace App\Filament\Resources\Penjuals\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class PenjualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Penjual')
                ->description(fn($context) => $context === 'create' ?
                    'Input data penjual baru & hutang awal' :
                    'Edit informasi penjual')
                ->schema([
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
                        ->label(fn($context) => $context === 'create' ?
                            'Hutang Awal' : 'Total Hutang')
                        ->helperText(fn($context) => $context === 'create' ?
                            'Masukkan hutang awal jika ada. Input ini hanya bisa dilakukan sekali saat pendaftaran penjual.' : '')
                        ->dehydrated()
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->currencyMask(
                            thousandSeparator: ',',
                            decimalSeparator: '.',
                            precision: 0
                        ),
                ])
                ->columns(2)
        ]);
    }
}
