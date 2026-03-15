<?php

namespace App\Filament\Resources\Kendaraans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class KendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Detail Kendaraan')
                            ->description('Informasi utama kendaraan')
                            ->components([
                                TextInput::make('no_polisi')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Penugasan')
                            ->description('Pengaturan supir')
                            ->components([
                                Select::make('supir_id')
                                    ->relationship('supir', 'nama')
                                    ->searchable()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
