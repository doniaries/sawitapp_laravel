<?php

namespace App\Filament\Resources\Supirs\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class SupirForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make()
                ->components([
                    Grid::make(4)
                        ->components([
                            Forms\Components\TextInput::make('nama')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('telepon')
                                ->tel(),

                            Forms\Components\TextInput::make('alamat'),

                            Forms\Components\TextInput::make('hutang')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0)
                                ->currencyMask(
                                    thousandSeparator: ',',
                                    decimalSeparator: '.',
                                    precision: 0
                                ),
                        ]),
                ])
        ]);
    }
}
