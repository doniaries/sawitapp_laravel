<?php

namespace App\Filament\Resources\Penjuals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class PenjualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Penjual')
                            ->description(fn($context) => $context === 'create' ?
                                'Input data diri penjual baru' :
                                'Edit informasi data diri penjual')
                            ->components([
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
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Detail Hutang')
                            ->description('Informasi awal hutang penjual')
                            ->components([
                                TextInput::make('hutang')
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
                            ]),
                    ]),
            ]);
    }
}
