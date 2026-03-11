<?php

namespace App\Models;

use App\Models\Operasional;
use App\Enums\KategoriOperasional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supir extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supir';

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
        'riwayat_bayar'
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
    ];

    // Relations
    public function transaksiDo(): HasMany
    {
        return $this->hasMany(TransaksiDo::class);
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
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
        return $this->hasMany(Operasional::class, 'supir_id')
            ->where('tipe_nama', 'supir')
            ->where('kategori', KategoriOperasional::PINJAMAN);
    }

    // Add relationship to RiwayatPembayaranHutang
    public function riwayatHutang()
    {
        return $this->hasMany(RiwayatPembayaranHutang::class);
    }

    // Add total pinjaman accessor
    public function getTotalPinjamanAttribute()
    {
        return $this->riwayatHutang()
            ->where('tipe', 'supir')
            ->sum('nominal');
    }

    // Add total hutang accessor

}
