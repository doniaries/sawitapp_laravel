<?php

namespace App\Filament\Resources\Supirs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class SupirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Supir')
                            ->description('Detail profil data diri supir')
                            ->components([
                                TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->debounce(500),

                                TextInput::make('telepon')
                                    ->tel()
                                    ->debounce(500),

                                TextInput::make('alamat')
                                    ->debounce(500),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Detail Hutang')
                            ->description('Informasi awal hutang supir')
                            ->components([
                                TextInput::make('hutang')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->currencyMask(
                                        thousandSeparator: '.',
                                        decimalSeparator: ',',
                                        precision: 0
                                    )
                                    ->debounce(500),
                                TextInput::make('sisa_hutang')
                                    ->label('Sisa Hutang Saat Ini')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('Rp')
                                    ->currencyMask(
                                        thousandSeparator: '.',
                                        decimalSeparator: ',',
                                        precision: 0
                                    ),
                            ]),
                    ]),
        ]);
    }
}
