<?php

namespace App\Models;

use App\Enums\KategoriOperasional; // [TAMBAH] Import enum
use App\Models\{User, Penjual, Supir, Pekerja, TransaksiDo}; // [EDIT] Gabungkan import
use Illuminate\Database\Eloquent\{Model, SoftDeletes, Factories\HasFactory}; // [EDIT] Gabungkan import
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\BelongsToTenant;

class TransaksiOperasional extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'transaksi_operasional';

    protected $fillable = [
        'perusahaan_id',
        'pihak_id',
        'pihak_type',
        'tanggal',
        'operasional',
        'kategori',
        'nominal',
        'keterangan',
        'tipe_nama',
        'user_id',
        'is_from_transaksi',
        // 'file_bukti',
    ];

    protected $casts = [
        'tanggal' => 'datetime', // [EDIT] Ubah ke datetime untuk tampilan jam
        'nominal' => 'decimal:0',
        'kategori' => KategoriOperasional::class, // [TAMBAH] Cast ke Enum
        'tipe_nama' => 'string',
    ];

    // [HAPUS] $dates karena sudah tercover oleh SoftDeletes dan casts

    const JENIS_OPERASIONAL = [ // [TETAP] Masih digunakan untuk validasi
        'pemasukan' => 'Pemasukan',
        'pengeluaran' => 'Pengeluaran',
    ];

    // Tambahkan property
    protected $hidden = [
        'max_pembayaran',
        'hutang_awal',
        'info_hutang'
    ];

    public function pihak()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo // [EDIT] Tambah return type
    {
        return $this->belongsTo(User::class);
    }

    // Accessors & Mutators
    public function getNamaAttribute(): ?string // [EDIT] Tambah return type
    {
        return $this->pihak?->nama ?? $this->pihak?->name ?? '-';
    }

    // [TAMBAH] Accessor untuk label kategori
    public function getKategoriLabelAttribute(): string
    {
        return $this->kategori?->label() ?? '-'; // Gunakan method label() bukan getLabel()
    }

    // Scopes
    public function scopeManualEntry($query): Builder // [EDIT] Tambah return type
    {
        return $query->where('is_from_transaksi', false);
    }

    public function scopeFromTransaksi($query): Builder // [EDIT] Tambah return type
    {
        return $query->where('is_from_transaksi', true);
    }


    // [TAMBAH] Boot method untuk auto-set jenis operasional
    protected static function booted(): void
    {
        static::saving(function ($operasional) {
            if ($operasional->kategori) {
                $operasional->operasional = $operasional->kategori->getJenisOperasional();
            }
        });
    }

    // Tambahkan accessor untuk memformat hutang
    public function getFormattedHutangAttribute(): string
    {
        return 'Rp ' . number_format($this->hutang_awal ?? 0, 0, ',', '.');
    }
}
