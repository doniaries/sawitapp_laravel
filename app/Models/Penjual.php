<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use App\Enums\KategoriOperasional;
use App\Traits\BelongsToTenant;

class Penjual extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'penjual';

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
        'perusahaan_id',
    ];

    protected $appends = [
        // 'sisa_hutang', // Dipindahkan ke database query level/explicit call untuk performa
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
    ];

    // Custom accessor for formatted hutang
    public function mutasiHutang()
    {
        return $this->morphMany(MutasiHutang::class, 'pihak');
    }

    public function getFormattedHutangAttribute()
    {
        return 'Rp ' . number_format($this->hutang, 0, ',', '.');
    }

    // Relationships with optimized queries
    public function transaksiDo(): HasMany
    {
        return $this->hasMany(TransaksiDo::class)
            ->latest();
    }


    //riwayat bayar
    public function jurnalKeuangan()
    {
        return $this->hasMany(JurnalKeuangan::class, 'pihak_terkait', 'nama')
            ->whereIn('sub_kategori', ['Bayar Hutang', 'Pinjaman'])
            ->orderBy('tanggal', 'desc');
    }

    public function updateHutang(float $amount, string $type = 'add'): void
    {
        if ($type === 'add') {
            $this->increment('hutang', $amount);
        } else {
            $this->decrement('hutang', $amount);
        }
    }

    //relation ship penjual dengan operasional
    public function operasional(): HasMany
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', self::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penjual) {
            $penjual->nama = mb_strtoupper($penjual->nama);
            $penjual->slug = Str::slug($penjual->nama);

            if ($penjual->hutang > 0) {
                \Illuminate\Support\Facades\Log::info('Input Hutang Awal Penjual:', [
                    'penjual' => $penjual->nama,
                    'hutang_awal' => $penjual->hutang,
                    'tanggal' => now(),
                    'user' => auth()->user()?->name ?? 'System'
                ]);
            }
        });

        static::updating(function ($penjual) {
            $penjual->nama = mb_strtoupper($penjual->nama);
            $penjual->slug = Str::slug($penjual->nama);
        });
    }

    // Scopes
    public function scopeWithTransaksiStats($query)
    {
        return $query->withCount('transaksiDo')
            ->withSum('transaksiDo', 'total');
    }

    public function scopeHasHutang($query)
    {
        return $query->where('hutang', '>', 0);
    }

    public function paymentHistory()
    {
        return $this->hasMany(TransaksiDo::class, 'penjual_id')
            ->select('id', 'pembayaran_hutang', 'created_at')
            ->orderBy('created_at', 'desc');
    }

    public function scopePinjaman($query)
    {
        return $query->whereHas('operasional', function ($q) {
            $q->where('kategori', KategoriOperasional::PINJAMAN);
        });
    }

    public function getTotalPinjamanAttribute()
    {
        return $this->operasional()
            ->where('kategori', KategoriOperasional::PINJAMAN)
            ->sum('nominal');
    }


    // Tambahkan relasi ke riwayat pembayaran
    public function riwayatPembayaran()
    {
        return $this->hasMany(PembayaranHutang::class)
            ->where('tipe_nama', 'penjual')
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
