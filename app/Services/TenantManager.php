<?php

namespace App\Services;

use App\Models\Perusahaan;

class TenantManager
{
    protected static ?Perusahaan $tenant = null;

    public static function setTenant(Perusahaan $tenant): void
    {
        self::$tenant = $tenant;
    }

    public static function getTenant(): ?Perusahaan
    {
        return self::$tenant;
    }

    public static function getTenantId(): ?int
    {
        return self::$tenant?->id;
    }
}
