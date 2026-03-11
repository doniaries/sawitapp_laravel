<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\{Operasional, TransaksiDo, LaporanKeuangan, RiwayatPembayaranHutang};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\KategoriOperasional;

class Penjual extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
    ];

    // Custom accessor for formatted hutang
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
    public function laporanKeuangan()
    {
        return $this->hasMany(LaporanKeuangan::class, 'pihak_terkait', 'nama')
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
        return $this->hasMany(Operasional::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Log hutang awal saat create
        static::creating(function ($penjual) {
            if ($penjual->hutang > 0) {
                \Log::info('Input Hutang Awal Penjual:', [
                    'penjual' => $penjual->nama,
                    'hutang_awal' => $penjual->hutang,
                    'tanggal' => now(),
                    'user' => auth()->user()->name ?? 'System'
                ]);
            }
        });

        // // Cegah perubahan hutang langsung dari form edit
        // static::updating(function ($penjual) {
        //     if ($penjual->isDirty('hutang') && !$penjual->wasRecentlyCreated) {
        //         throw new \Exception('Hutang hanya bisa diubah melalui transaksi.');
        //     }
        // });
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
            $q->where('kategori', KategoriOperasional::PINJAMAN)
                ->where('tipe_nama', 'penjual');
        });
    }

    public function getTotalPinjamanAttribute()
    {
        return $this->operasional()
            ->where('kategori', KategoriOperasional::PINJAMAN)
            ->where('tipe_nama', 'penjual')
            ->sum('nominal');
    }


    // Tambahkan relasi ke riwayat pembayaran
    public function riwayatPembayaran()
    {
        return $this->hasMany(RiwayatPembayaranHutang::class)
            ->where('tipe', 'penjual')
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