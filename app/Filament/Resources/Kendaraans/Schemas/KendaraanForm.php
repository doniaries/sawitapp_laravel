<?php

namespace App\Filament\Resources\Kendaraans\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class KendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('no_polisi')
                                ->required()
                                ->maxLength(10),

                            Forms\Components\Select::make('supir_id')
                                ->relationship('supir', 'nama')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                ])
        ]);
    }
}
