<?php
 
namespace App\Filament\Resources\Users\Schemas;
 
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
 
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
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Abaikan jika tidak ingin merubah password' : null)
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
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Abaikan jika tidak ingin merubah password' : null)
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
                                    ->options(function() {
                                        return Role::whereIn('name', ['admin', 'kasir'])
                                            ->pluck('name', 'name')
                                            ->toArray();
                                    })
                                    ->afterStateHydrated(function (Select $component, ?User $record) {
                                        if ($record) {
                                            $role = $record->roles()->first();
                                            if ($role) {
                                                $component->state($role->name);
                                            }
                                        }
                                    })
                                    ->saveRelationshipsUsing(function (User $record, $state) {
                                        if (filled($state)) {
                                            $tenantId = \Filament\Facades\Filament::getTenant()?->id;
                                            
                                            // Handle Role Assignment
                                            if ($tenantId) {
                                                setPermissionsTeamId($tenantId);
                                            }
                                            $record->syncRoles([$state]);
 
                                            // Handle Global Access for Admin
                                            if ($state === 'admin') {
                                                $allPerusahaanIds = \App\Models\Perusahaan::pluck('id')->toArray();
                                                $record->perusahaans()->syncWithoutDetaching($allPerusahaanIds);
                                                
                                                // Ensure Admin role in all companies
                                                foreach ($allPerusahaanIds as $pId) {
                                                    setPermissionsTeamId($pId);
                                                    $record->assignRole('admin');
                                                }
                                                
                                                // Reset back to current tenant
                                                if ($tenantId) {
                                                    setPermissionsTeamId($tenantId);
                                                }
                                            }
                                        }
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->required(),
 
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
