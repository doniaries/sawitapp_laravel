<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
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
                                    ->maxLength(255)
                                    ->debounce(500),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->debounce(500),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable(true)
                                    ->helperText(fn(string $operation): ?string => $operation === 'edit' ? 'Abaikan jika tidak ingin merubah password' : null)
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
                                    ->helperText(fn(string $operation): ?string => $operation === 'edit' ? 'Abaikan jika tidak ingin merubah password' : null)
                                    ->required(
                                        fn(string $operation, Get $get): bool =>
                                        $operation === 'create' || filled($get('password'))
                                    )
                                    ->visible(true) // Selalu tampak di create dan edit sesuai permintaan
                                    ->minLength(6)
                                    ->maxLength(50)
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
                                Select::make('roles')
                                    ->label('Hak Akses')
                                    ->relationship('roles', 'name', function (Builder $query) {
                                        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
                                        return $query->where(function ($q) use ($tenantId) {
                                            if ($tenantId) {
                                                $q->where('roles.perusahaan_id', $tenantId)
                                                    ->orWhereNull('roles.perusahaan_id');
                                            }
                                        });
                                    })
                                    ->loadStateFromRelationshipsUsing(function (Select $component, ?User $record) {
                                        if ($record) {
                                            $tenantId = \Filament\Facades\Filament::getTenant()?->id;
                                            if ($tenantId) {
                                                setPermissionsTeamId($tenantId);
                                            }
                                            $role = $record->roles()->first();
                                            if ($role) {
                                                $component->state($role->id);
                                            }
                                        }
                                    })
                                    ->saveRelationshipsUsing(function (User $record, $state) {
                                        if (filled($state)) {
                                            $role = \Spatie\Permission\Models\Role::find($state);
                                            if ($role) {
                                                $tenantId = \Filament\Facades\Filament::getTenant()?->id;

                                                // Role assignment for current tenant
                                                if ($tenantId) {
                                                    setPermissionsTeamId($tenantId);
                                                }
                                                $record->syncRoles([$role->name]);

                                                // Global Access for Admin
                                                if ($role->name === 'admin' || $role->name === 'super_admin') {
                                                    $allPerusahaanIds = \App\Models\Perusahaan::pluck('id')->toArray();
                                                    $record->perusahaans()->syncWithoutDetaching($allPerusahaanIds);

                                                    foreach ($allPerusahaanIds as $pId) {
                                                        setPermissionsTeamId($pId);
                                                        $record->assignRole($role->name);
                                                    }

                                                    if ($tenantId) {
                                                        setPermissionsTeamId($tenantId);
                                                    }
                                                }
                                            }
                                        }
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                FileUpload::make('photo')
                                    ->label('Foto')
                                    ->disk('public')
                                    ->directory('users')
                                    ->visibility('public'),
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
