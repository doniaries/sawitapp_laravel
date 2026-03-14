<?php

namespace App\Models;

use App\Enums\TipeNama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranHutang extends Model
{
    use SoftDeletes;

    protected $table = 'pembayaran_hutang';

    protected $fillable = [
        'tanggal',
        'nominal',
        'tipe_nama',
        'penjual_id',
        'pekerja_id',
        'supir_id',
        'operasional_id',
        'keterangan',
        'perusahaan_id',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
        'tipe_nama' => TipeNama::class,
    ];

    // Relations
    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Penjual::class);
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function supir()
    {
        return $this->belongsTo(Supir::class);
    }

    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class);
    }

    public function operasional(): BelongsTo
    {
        return $this->belongsTo(TransaksiOperasional::class);
    }

    // Helper method untuk format nominal
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    // Helper untuk ambil nama pembayar
    public function getNamaPembayarAttribute(): string
    {
        return match ($this->tipe) {
            'penjual' => $this->penjual?->nama ?? '-',
            'pekerja' => $this->pekerja?->nama ?? '-',
            'supir' => $this->supir?->nama ?? '-',
            default => '-'
        };
    }
}
