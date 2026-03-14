<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pabrik extends Model
{
    protected $fillable = [
        'perusahaan_id',
        'nama',
        'alamat',
        'keterangan',
    ];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
