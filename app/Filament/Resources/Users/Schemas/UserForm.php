<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pengguna')
                    ->description('Kelola informasi pengguna dan akses')
                    ->schema([
                        Forms\Components\Select::make('perusahaan_id')
                            ->label('Perusahaan')
                            ->relationship('perusahaan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->unique(ignoreRecord: true)
                            ->email()
                            ->required()
                            ->maxLength(20),

                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required()
                            ->label('Hak Akses'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable(true)
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(6)
                            ->maxLength(50)
                            ->same('passwordConfirmation')
                            ->dehydrated(fn($state) => filled($state))
                            ->live(true),

                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable(true)
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->required(fn(string $operation, Get $get): bool =>
                                $operation === 'create' || filled($get('password'))
                            )
                            ->visible(fn(string $operation, Get $get): bool =>
                                $operation === 'create' || filled($get('password'))
                            )
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
