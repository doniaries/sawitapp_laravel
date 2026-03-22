<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_version;
    public string $app_creator;

    public static function group(): string
    {
        return 'general';
    }
}
