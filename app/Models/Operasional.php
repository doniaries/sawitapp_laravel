<?php

namespace App\Models;

use App\Enums\KategoriOperasional; // [TAMBAH] Import enum
use App\Models\{User, Penjual, Supir, Pekerja, TransaksiDo}; // [EDIT] Gabungkan import
use Illuminate\Database\Eloquent\{Model, SoftDeletes, Factories\HasFactory}; // [EDIT] Gabungkan import
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operasional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasional';

    protected $fillable = [
        'tanggal',
        'operasional',
        'kategori',
        'tipe_nama',
        'penjual_id',
        'supir_id',
        'user_id',
        'pekerja_id', // [TAMBAH] Sesuai dengan relasi
        'nominal',
        'keterangan',
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

    // Relations
    public function penjual(): BelongsTo // [EDIT] Tambah return type
    {
        return $this->belongsTo(Penjual::class);
    }


    public function supir()
    {
        return $this->belongsTo(Supir::class);
    }


    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class);
    }

    public function user(): BelongsTo // [EDIT] Tambah return type
    {
        return $this->belongsTo(User::class);
    }

    public function transaksiDo(): BelongsTo // [EDIT] Tambah return type
    {
        return $this->belongsTo(TransaksiDo::class, 'transaksi_do_id');
    }

    // Accessors & Mutators
    public function getNamaAttribute(): ?string // [EDIT] Tambah return type
    {
        return match ($this->tipe_nama) {
            'penjual' => $this->penjual?->nama,
            'user' => $this->user?->name,
            'pekerja' => $this->pekerja?->nama, // [TAMBAH] Handle pekerja
            default => null
        };
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
