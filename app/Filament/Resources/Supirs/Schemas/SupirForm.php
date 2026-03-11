<?php

namespace App\Filament\Resources\Supirs\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class SupirForm
{
    public static function configure(\Filament\Forms\Form $schema): \Filament\Forms\Form
    {
        return $schema->schema([
            Section::make()
                ->schema([
                    Grid::make(4)
                        ->schema([
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
