<?php

namespace App\Filament\Resources\Perusahaans\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class PerusahaanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1])
                ->components([
                    Section::make('Informasi Dasar')
                        ->components([
                            Grid::make(2)
                                ->components([
                                    TextInput::make('name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('saldo')
                                        ->required()
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->required()
                                        ->prefix('Rp.'),
                                    TextInput::make('alamat')
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email(),
                                    TextInput::make('pimpinan')
                                        ->maxLength(255),
                                    TextInput::make('npwp')
                                        ->maxLength(30),
                                    FileUpload::make('logo')
                                        ->label('Logo Perusahaan')
                                        ->image()
                                        ->disk('public')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->preserveFilenames(),

                                    Toggle::make('is_active')
                                        ->required(),
                                ]),
                        ]),
                ]),
        ]);
    }
}
