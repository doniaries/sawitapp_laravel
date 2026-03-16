<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToTenant;

class Kendaraan extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'kendaraan';

    protected $fillable = [
        'perusahaan_id',
        'nama',
        'no_polisi',
        'is_maintenance',
    ];

    protected $casts = [
        'is_maintenance' => 'boolean',
    ];

    public function scopeIsNotMaintenance($query)
    {
        return $query->where('is_maintenance', false);
    }
}
