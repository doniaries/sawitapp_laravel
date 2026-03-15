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
            if (Filament::hasTenancy() && Filament::getTenant()) {
                $builder->where($builder->qualifyColumn('perusahaan_id'), Filament::getTenant()->id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->perusahaan_id) && Filament::hasTenancy() && Filament::getTenant()) {
                $model->perusahaan_id = Filament::getTenant()->id;
            }
        });
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
}
