<?php

namespace App\Filament\Resources\Perusahaans\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class PerusahaanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Dasar')
                            ->description('Keterangan profil perusahaan')
                            ->components([
                                TextInput::make('name')
                                    ->label('Nama Perusahaan')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('pimpinan')
                                    ->maxLength(255),
                                TextInput::make('alamat')
                                    ->maxLength(255),
                                FileUpload::make('logo')
                                    ->label('Logo Perusahaan')
                                    ->image()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->preserveFilenames(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Detail Bisnis')
                            ->description('Informasi finansial dan kontak')
                            ->components([
                                TextInput::make('saldo')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp.'),
                                TextInput::make('email')
                                    ->email(),
                                TextInput::make('npwp')
                                    ->maxLength(30),
                                Toggle::make('is_active')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
