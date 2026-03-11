<?php

namespace App\Filament\Resources\Pekerjas\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class PekerjaForm
{
    public static function configure(\Filament\Forms\Form $schema): \Filament\Forms\Form
    {
        return $schema->schema([
            Forms\Components\TextInput::make('nama')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('alamat')
                ->maxLength(255),
            Forms\Components\TextInput::make('telepon')
                ->tel()
                ->maxLength(255),
            Forms\Components\TextInput::make('pendapatan')
                ->disabled()
                ->prefix('Rp. ')
                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                ->default(0),
            Forms\Components\TextInput::make('total_hutang')
                ->disabled()
                ->prefix('Rp. ')
                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                ->default(0),
        ]);
    }
}
