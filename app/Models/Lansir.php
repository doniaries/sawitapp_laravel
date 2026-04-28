<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lansir extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'perusahaan_id',
        'tanggal_lansir',
        'nama_supir',
        'nama_penjual',
        'tonase',
        'harga_satuan',
        'total',
        'upah',
    ];

    protected $casts = [
        'tanggal_lansir' => 'date',
        'tonase' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total' => 'decimal:2',
        'upah' => 'decimal:2',
    ];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
