<?php

namespace App\Filament\Pages\Tenancy;

use App\Filament\Resources\Perusahaans\Schemas\PerusahaanForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Profil Perusahaan';
    }

    public function form(Schema $schema): Schema
    {
        return PerusahaanForm::configure($schema);
    }
}
