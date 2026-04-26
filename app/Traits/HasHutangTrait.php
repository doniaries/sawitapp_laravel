<?php

namespace App\Traits;

use App\Models\PembayaranHutang;
use App\Models\TransaksiOperasional;
use App\Enums\KategoriOperasional;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasHutangTrait
{
    public static function bootHasHutangTrait()
    {
        static::creating(function ($model) {
            if (isset($model->nama)) {
                $model->nama = mb_strtoupper($model->nama);
                $model->slug = Str::slug($model->nama);
            }
        });

        static::updating(function ($model) {
            if (isset($model->nama)) {
                $model->nama = mb_strtoupper($model->nama);
                $model->slug = Str::slug($model->nama);
            }
        });
    }

    /**
     * Relasi ke riwayat pembayaran hutang
     */
    public function riwayatPembayaran(): HasMany
    {
        $foreignKey = match (get_class($this)) {
            \App\Models\Penjual::class => 'penjual_id',
            \App\Models\Supir::class => 'supir_id',
            \App\Models\Pekerja::class => 'pekerja_id',
            default => null,
        };

        if ($foreignKey) {
            return $this->hasMany(PembayaranHutang::class, $foreignKey)
                ->orderBy('tanggal', 'desc');
        }

        // Fallback to morph-like check if needed, but currently uses explicit IDs
        return $this->hasMany(PembayaranHutang::class)
            ->where('tipe_nama', strtolower(class_basename($this)))
            ->orderBy('tanggal', 'desc');
    }

    /**
     * Relasi ke transaksi operasional (Pinjaman)
     */
    public function pinjaman(): HasMany
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', get_class($this))
            ->where('kategori', KategoriOperasional::PINJAMAN);
    }

    /**
     * Accessor untuk Total Pembayaran
     */
    public function getTotalPembayaranAttribute(): float
    {
        // Check standard naming conventions for withSum()
        $keys = [
            'riwayat_pembayaran_sum_nominal',
            'total_pembayaran_sum',
            'riwayatPembayaran_sum_nominal'
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->attributes)) {
                return (float) $this->attributes[$key];
            }
        }

        return (float) $this->riwayatPembayaran()->sum('nominal');
    }

    /**
     * Accessor untuk Sisa Hutang (Real-time)
     */
    public function getSisaHutangAttribute(): float
    {
        return (float) ($this->hutang ?? 0) - $this->total_pembayaran;
    }

    /**
     * Accessor untuk Total Pinjaman
     */
    public function getTotalPinjamanAttribute(): float
    {
        return (float) $this->pinjaman()->sum('nominal');
    }

    /**
     * Format Rupiah untuk Hutang
     */
    public function getFormattedHutangAttribute(): string
    {
        return 'Rp ' . number_format($this->hutang ?? 0, 0, ',', '.');
    }

    /**
     * Validasi pembayaran agar tidak melebihi sisa hutang
     */
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
