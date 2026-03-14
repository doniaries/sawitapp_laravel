<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Perusahaan extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected static function booted()
    {
        static::creating(fn($perusahaan) => $perusahaan->slug = Str::slug($perusahaan->name));
        static::updating(fn($perusahaan) => $perusahaan->slug = Str::slug($perusahaan->name));
    }

    protected $table = 'perusahaans';

    protected $fillable = [
        'name',
        'alamat',
        'email',
        'telepon',
        'pimpinan',
        'is_active',
        'saldo',
        'npwp',
        'no_izin_usaha',
        'logo',
        'slug',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'saldo' => 'decimal:0',
        'setting' => 'json',
    ];

    public function getCurrentTenantLabel(): string
    {
        return 'Active team';
    }

    protected function logo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return url('/images/default-logo.png');
                }

                return Storage::disk('public')->url($value);
            },
        );
    }


    public function rollbackSaldo(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            $this->decrement('saldo', $amount);
        });
    }

    // Helper method untuk format saldo
    public function getFormattedSaldoAttribute()
    {
        return 'Rp ' . number_format($this->saldo, 0, ',', '.');
    }

    public function riwayatSaldo()
    {
        return $this->hasMany(LaporanKeuangan::class, 'referensi_id')
            ->where('kategori', 'Saldo')
            ->where('sub_kategori', 'Tambah Saldo')
            ->orderBy('tanggal', 'desc');
    }
}
