<?php

namespace App\Traits;

use App\Models\PembayaranHutang;
use App\Models\TransaksiOperasional;
use App\Enums\KategoriOperasional;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\MutasiHutang;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property-read float $total_pembayaran
 * @property-read float $sisa_hutang
 * @property-read float $total_pinjaman
 * @property-read string $formatted_hutang
 * @property float $hutang
 * @property string $nama
 * @property string $slug
 * @method static void creating(\Closure $callback)
 * @method static void updating(\Closure $callback)
 * @method bool hasAttribute(string $key)
 * @method mixed getAttribute(string $key)
 * @method void setAttribute(string $key, mixed $value)
 * @method string getTable()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany hasMany(string $related, string $foreignKey = null, string $localKey = null)
 */
trait HasHutangTrait
{
    public static function bootHasHutangTrait()
    {
        static::creating(function ($model) {
            if ($model->hasAttribute('nama')) {
                $model->setAttribute('nama', mb_strtoupper($model->getAttribute('nama')));
                $model->setAttribute('slug', Str::slug($model->getAttribute('nama')));
            }
        });

        static::updating(function ($model) {
            if ($model->hasAttribute('nama')) {
                $model->setAttribute('nama', mb_strtoupper($model->getAttribute('nama')));
                $model->setAttribute('slug', Str::slug($model->getAttribute('nama')));
            }
        });
    }

    /**
     * Relasi ke mutasi hutang (Unified Ledger)
     */
    public function mutasiHutang(): MorphMany
    {
        return $this->morphMany(MutasiHutang::class, 'pihak')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');
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
     * Scope untuk memuat sisa hutang secara efisien
     */
    public function scopeWithSisaHutang(Builder $query): Builder
    {
        $table = $this->getTable();
        $foreignKey = match (get_class($this)) {
            \App\Models\Penjual::class => 'penjual_id',
            \App\Models\Supir::class => 'supir_id',
            \App\Models\Pekerja::class => 'pekerja_id',
            default => null,
        };

        if (!$foreignKey) {
            return $query;
        }

        return $query->addSelect([
            'total_pembayaran_sum' => PembayaranHutang::query()->selectRaw('COALESCE(SUM(nominal), 0)')
                ->where($foreignKey, "{$table}.id")
        ])->selectRaw("({$table}.hutang - (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_hutang WHERE {$foreignKey} = {$table}.id)) as sisa_hutang_sum");
    }

    /**
     * Accessor untuk Total Pembayaran
     */
    public function getTotalPembayaranAttribute(): float
    {
        // Check standard naming conventions for withSum()
        $keys = [
            'total_pembayaran_sum',
            'riwayat_pembayaran_sum_nominal',
            'riwayatPembayaran_sum_nominal'
        ];

        foreach ($keys as $key) {
            if ($this->hasAttribute($key)) {
                return (float) $this->getAttribute($key);
            }
        }

        return (float) $this->riwayatPembayaran()->sum('nominal');
    }

    /**
     * Accessor untuk Sisa Hutang (Real-time)
     */
    public function getSisaHutangAttribute(): float
    {
        if ($this->hasAttribute('sisa_hutang_sum')) {
            return (float) $this->getAttribute('sisa_hutang_sum');
        }

        return (float) ($this->getAttribute('hutang') ?? 0) - $this->total_pembayaran;
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
        return (string) money($this->getAttribute('hutang') ?? 0, 'IDR');
    }

    /**
     * Validasi pembayaran agar tidak melebihi sisa hutang
     */
    public function validatePayment(float $nominal): bool
    {
        if ($nominal > $this->sisa_hutang) {
            throw new \Exception(
                "Pembayaran " . money($nominal, 'IDR') .
                    " melebihi sisa hutang " . money($this->sisa_hutang, 'IDR')
            );
        }
        return true;
    }
}

