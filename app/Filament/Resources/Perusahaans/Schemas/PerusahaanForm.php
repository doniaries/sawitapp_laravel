<?php

namespace App\Filament\Resources\Perusahaans\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class PerusahaanForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Grid::make(['default' => 1])
                ->schema([
                    Section::make('Informasi Dasar')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('saldo')
                                        ->required()
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->required()
                                        ->prefix('Rp.'),
                                    Forms\Components\TextInput::make('alamat')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->email(),
                                    Forms\Components\TextInput::make('pimpinan')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('npwp')
                                        ->maxLength(30),
                                    Forms\Components\FileUpload::make('logo')
                                        ->label('Logo Perusahaan')
                                        ->image()
                                        ->disk('public')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->preserveFilenames(),

                                    Forms\Components\Toggle::make('is_active')
                                        ->required(),
                                ]),
                        ]),
                ]),
        ]);
    }
}
