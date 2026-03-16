<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Pengguna')
                            ->description('Kelola informasi dasar pengguna')
                            ->components([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('password')
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

                                TextInput::make('passwordConfirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable(true)
                                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                    ->required(
                                        fn(string $operation, \Filament\Schemas\Components\Utilities\Get $get): bool =>
                                        $operation === 'create' || filled($get('password'))
                                    )
                                    ->visible(
                                        fn(string $operation, \Filament\Schemas\Components\Utilities\Get $get): bool =>
                                        $operation === 'create' || filled($get('password'))
                                    )
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->dehydrated(false),
                            ])
                            ->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Kredensial & Otorisasi')
                            ->description('Pengaturan hak akses dan perusahaan')
                            ->components([
                                Select::make('perusahaans')
                                    ->label('Perusahaan')
                                    ->relationship('perusahaans', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->maxItems(function (Get $get) {
                                        $roles = $get('roles') ?? [];
                                        if (in_array('kasir', $roles)) {
                                            return 1;
                                        }
                                        return 2;
                                    })
                                    ->required(fn (Get $get) => !in_array('super_admin', $get('roles') ?? []))
                                    ->visible(fn (Get $get) => !in_array('super_admin', $get('roles') ?? []))
                                    ->native(false),

                                Select::make('roles')
                                    ->label('Hak Akses')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
