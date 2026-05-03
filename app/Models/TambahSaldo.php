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
        'client_uuid',
        'client_created_at',
        'client_updated_at',
        'synced_at',
        'perusahaan_id',
        'user_id',
        'tanggal',
        'nominal',
        'keterangan',
        'bukti_transfer',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'client_created_at' => 'datetime',
        'client_updated_at' => 'datetime',
        'synced_at' => 'datetime',
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
