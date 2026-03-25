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
        'perusahaan_id',
        'nama',
        'slug',
        'alamat',
        'telepon',
        'hutang',
        'is_maintenance',
        'status_supir',
    ];

    protected $appends = [
        // 'sisa_hutang', // Dipindahkan ke database query level/explicit call untuk performa
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

    public function jurnalKeuangan(): HasMany
    {
        return $this->hasMany(JurnalKeuangan::class, 'pihak_terkait', 'nama')
            ->orderBy('tanggal', 'desc');
    }

    // Scopes
    public function scopeIsNotMaintenance($query)
    {
        return $query->where('is_maintenance', false);
    }



    // Helpers
    public function mutasiHutang()
    {
        return $this->morphMany(MutasiHutang::class, 'pihak');
    }

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
            ->where('pihak_type', self::class)
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
        return $this->pinjaman()
            ->sum('nominal');
    }

    // Add relationship with kendaraan

    // Tambahkan relasi ke riwayat pembayaran
    public function riwayatPembayaran()
    {
        return $this->hasMany(PembayaranHutang::class, 'supir_id')
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
