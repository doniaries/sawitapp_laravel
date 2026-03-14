<?php

namespace App\Filament\Resources\Kendaraans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class KendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('no_polisi')
                                ->required()
                                ->maxLength(10),

                            Select::make('supir_id')
                                ->relationship('supir', 'nama')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                ])
        ]);
    }
}
