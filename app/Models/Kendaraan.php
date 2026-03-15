<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\BelongsToTenant;

class Kendaraan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'kendaraan';

    protected $fillable = [
        'no_polisi',
        'supir_id',
        'perusahaan_id',
    ];

    // Relations
    public function supir(): BelongsTo
    {
        return $this->belongsTo(Supir::class);
    }
}