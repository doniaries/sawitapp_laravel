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
    use HasFactory, SoftDeletes, BelongsToTenant, \App\Traits\HasHutangTrait;

    protected $table = 'penjual';

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'hutang',
        'perusahaan_id',
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
    ];

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
}
