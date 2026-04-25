<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class TambahSaldo extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'tambah_saldo';
    protected $fillable = [
        'perusahaan_id',
        'user_id',
        'tanggal',
        'nominal',
        'keterangan',
        'bukti_transfer',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
}
