<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToTenant;

class Pekerja extends Model
{
    use SoftDeletes, BelongsToTenant;
    protected $table = 'pekerja';

    protected static function booted()
    {
        static::creating(function ($pekerja) {
            $pekerja->nama = mb_strtoupper($pekerja->nama);
            $pekerja->slug = Str::slug($pekerja->nama);
        });
        static::updating(function ($pekerja) {
            $pekerja->nama = mb_strtoupper($pekerja->nama);
            $pekerja->slug = Str::slug($pekerja->nama);
        });
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

    protected $appends = [
        // 'sisa_hutang', // Dipindahkan ke database query level/explicit call untuk performa
    ];

    protected $casts = [
        'pendapatan' => 'decimal:0',
        'hutang' => 'decimal:0',
    ];


    public function operasional()
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', self::class);
    }

    public function pinjaman()
    {
        return $this->operasional()
            ->where('kategori', \App\Enums\KategoriOperasional::PINJAMAN);
    }

    public function getTotalPinjamanAttribute()
    {
        return $this->pinjaman()->sum('nominal');
    }

    public function jurnalKeuangan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JurnalKeuangan::class, 'pihak_terkait', 'nama')
            ->orderBy('tanggal', 'desc');
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
        if (array_key_exists('riwayat_pembayaran_sum_nominal', $this->attributes)) {
            return (float) $this->attributes['riwayat_pembayaran_sum_nominal'];
        }

        return (float) $this->riwayatPembayaran()->sum('nominal');
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
