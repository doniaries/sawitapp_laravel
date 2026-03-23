<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;

class ManageSettings extends SettingsPage
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Pengaturan';

    protected static string $settings = GeneralSettings::class;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }

    public static function isScopedToTenant(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aplikasi')
                    ->description('Kelola versi dan nama pembuat aplikasi.')
                    ->schema([
                        TextInput::make('app_version')
                            ->label('Versi Aplikasi')
                            ->required(),
                        TextInput::make('app_creator')
                            ->label('Nama Pembuat')
                            ->required(),
                    ]),
            ]);
    }
}
