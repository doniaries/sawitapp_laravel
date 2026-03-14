<?php

namespace App\Filament\Resources\Pekerjas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class PekerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Pekerja')
                            ->description('Detail profil data diri pekerja')
                            ->components([
                                TextInput::make('nama')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('alamat')
                                    ->maxLength(255),
                                TextInput::make('telepon')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Statistik Keuangan')
                            ->description('Ringkasan data keuangan terkait')
                            ->components([
                                TextInput::make('pendapatan')
                                    ->disabled()
                                    ->prefix('Rp. ')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->default(0),
                                TextInput::make('total_hutang')
                                    ->disabled()
                                    ->prefix('Rp. ')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->default(0),
                            ]),
                    ]),
        ]);
    }
}
