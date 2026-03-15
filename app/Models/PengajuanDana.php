<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class PengajuanDana extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'pengajuan_dana';

    protected $fillable = [
        'perusahaan_id',
        'user_id',
        'tanggal_pengajuan',
        'nominal',
        'keperluan',
        'status',
        'tanggal_proses',
        'proses_by',
        'catatan_pimpinan',
        'bukti_transfer',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_proses' => 'datetime',
        'nominal' => 'decimal:0',
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK = 'ditolak';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proses_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_DISETUJUI => 'success',
            self::STATUS_DITOLAK => 'danger',
            default => 'gray',
        };
    }
}
