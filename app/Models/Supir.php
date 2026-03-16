<?php

namespace App\Models;

use App\Models\Operasional;
use App\Enums\KategoriOperasional;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToTenant;

class Supir extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected static function booted()
    {
        static::creating(fn ($supir) => $supir->slug = Str::slug($supir->nama));
        static::updating(fn ($supir) => $supir->slug = Str::slug($supir->nama));
    }

    protected $table = 'supir';

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
        'riwayat_bayar',
        'perusahaan_id',
        'slug',
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
        'is_maintenance' => 'boolean',
    ];

    // Relations
    public function transaksiDo(): HasMany
    {
        return $this->hasMany(TransaksiDo::class);
    }

    // Scopes
    public function scopeIsNotMaintenance($query)
    {
        return $query->where('is_maintenance', false);
    }



    // Helpers
    public function getFormattedHutangAttribute(): string
    {
        return 'Rp ' . number_format($this->hutang ?? 0, 0, ',', '.');
    }

    // Scopes
    public function scopeHasHutang($query)
    {
        return $query->where('hutang', '>', 0);
    }

    public function scopeWithTransaksiStats($query)
    {
        return $query->withCount('transaksiDo')
            ->withSum('transaksiDo', 'total');
    }

    // Add relationship with Operasional
    public function pinjaman()
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', Supir::class)
            ->where('kategori', KategoriOperasional::PINJAMAN);
    }

    // Add relationship to RiwayatPembayaranHutang
    public function riwayatHutang()
    {
        return $this->hasMany(PembayaranHutang::class);
    }

    // Add total pinjaman accessor
    public function getTotalPinjamanAttribute()
    {
        return $this->riwayatHutang()
            ->where('tipe_nama', 'supir')
            ->sum('nominal');
    }

    // Add relationship with kendaraan

    // Add total hutang accessor

}
