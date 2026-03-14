<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pekerja extends Model
{
    use SoftDeletes;
    protected $table = 'pekerja';

    protected static function booted()
    {
        static::creating(fn ($pekerja) => $pekerja->slug = Str::slug($pekerja->nama));
        static::updating(fn ($pekerja) => $pekerja->slug = Str::slug($pekerja->nama));
    }

    protected $fillable = [
        'id',
        'nama',
        'alamat',
        'telepon',
        'pendapatan',
        'hutang',
        'perusahaan_id',
        'slug',
    ];

    protected $casts = [
        'pendapatan' => 'decimal:0',
        'hutang' => 'decimal:0',
    ];


    public function operasional()
    {
        return $this->hasMany(TransaksiOperasional::class);
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    // Tambahkan relasi ke riwayat pembayaran
    public function riwayatPembayaran()
    {
        return $this->hasMany(PembayaranHutang::class)
            ->where('tipe_nama', 'pekerja')
            ->orderBy('tanggal', 'desc');
    }

    // Method untuk get total pembayaran
    public function getTotalPembayaranAttribute(): float
    {
        return $this->riwayatPembayaran()
            ->sum('nominal');
    }

    // Method untuk get sisa hutang real-time
    public function getSisaHutangAttribute(): float
    {
        return $this->hutang - $this->total_pembayaran;
    }

    // Method untuk validasi pembayaran
    public function validatePayment(float $nominal): bool
    {
        if ($nominal > $this->sisa_hutang) {
            throw new \Exception(
                "Pembayaran Rp " . number_format($nominal, 0, ',', '.') .
                    " melebihi sisa hutang Rp " . number_format($this->sisa_hutang, 0, ',', '.')
            );
        }
        return true;
    }
}
