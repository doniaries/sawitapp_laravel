<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MutasiHutang extends Model
{
    protected $table = 'mutasi_hutang';

    protected $fillable = [
        'perusahaan_id',
        'pihak_id',
        'pihak_type',
        'tanggal',
        'tipe',
        'nominal',
        'saldo_akhir',
        'referensi_id',
        'referensi_type',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function pihak(): MorphTo
    {
        return $this->morphTo();
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}
