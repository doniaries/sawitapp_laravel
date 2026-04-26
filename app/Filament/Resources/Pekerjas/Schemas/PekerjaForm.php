<?php

namespace App\Filament\Resources\Pekerjas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

use App\Filament\Resources\Common\ResourceSchema;

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
                        ResourceSchema::getContactSection('Pekerja'),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Info Pendapatan')
                            ->compact()
                            ->components([
                                TextInput::make('pendapatan')
                                    ->label('Total Pendapatan')
                                    ->disabled()
                                    ->prefix('Rp')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->default(0),
                            ]),
                        ResourceSchema::getHutangSection(),
                    ]),
        ]);
    }
}

