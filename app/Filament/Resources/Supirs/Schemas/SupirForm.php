<?php

namespace App\Filament\Resources\Supirs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class SupirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->components([
                    Grid::make(4)
                        ->components([
                            TextInput::make('nama')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('telepon')
                                ->tel(),

                            TextInput::make('alamat'),

                            TextInput::make('hutang')
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
