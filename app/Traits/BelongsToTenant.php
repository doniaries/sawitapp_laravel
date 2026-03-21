<?php

namespace App\Traits;

use App\Models\Perusahaan;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('perusahaan_id', function (Builder $builder) {
            $tenantId = null;

            if (Filament::hasTenancy() && Filament::getTenant()) {
                $tenantId = Filament::getTenant()->id;
            } elseif (\App\Services\TenantManager::getTenantId()) {
                $tenantId = \App\Services\TenantManager::getTenantId();
            }

            if ($tenantId) {
                $builder->where($builder->qualifyColumn('perusahaan_id'), $tenantId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->perusahaan_id)) {
                $tenantId = null;
                if (Filament::hasTenancy() && Filament::getTenant()) {
                    $tenantId = Filament::getTenant()->id;
                } elseif (\App\Services\TenantManager::getTenantId()) {
                    $tenantId = \App\Services\TenantManager::getTenantId();
                }

                if ($tenantId) {
                    $model->perusahaan_id = $tenantId;
                }
            }
        });
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
}
