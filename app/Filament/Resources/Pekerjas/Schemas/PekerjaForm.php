<?php

namespace App\Filament\Resources\Pekerjas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class PekerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('alamat')
                ->maxLength(255),
            TextInput::make('telepon')
                ->tel()
                ->maxLength(255),
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
        ]);
    }
}
