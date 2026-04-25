<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class Pabrik extends Model
{
    use BelongsToTenant;

    protected $table = 'pabrik';

    protected static function booted()
    {
        static::creating(fn ($pabrik) => $pabrik->slug = Str::slug($pabrik->nama));
        static::updating(fn ($pabrik) => $pabrik->slug = Str::slug($pabrik->nama));
    }

    protected $fillable = [
        'perusahaan_id',
        'nama',
        'alamat',
        'keterangan',
        'slug',
    ];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
