<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_version', '1.0.0');
        $this->migrator->add('general.app_creator', 'Success Mandiri');
    }
};
