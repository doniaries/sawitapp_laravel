<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';

    protected $fillable = [
        'no_polisi',
        'supir_id'
    ];

    // Relations
    public function supir(): BelongsTo
    {
        return $this->belongsTo(Supir::class);
    }
}