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

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class Perusahaan extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected static function booted()
    {
        static::creating(fn($perusahaan) => $perusahaan->slug = Str::slug($perusahaan->name));
        static::updating(fn($perusahaan) => $perusahaan->slug = Str::slug($perusahaan->name));
    }

    protected $table = 'perusahaan';

    protected $fillable = [
        'name',
        'type',
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
        'nama_kasir',
        'kasir_id',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'saldo' => 'decimal:0',
        'setting' => 'json',
    ];

    public function getCurrentTenantLabel(): string
    {
        return 'Active team';
    }

    protected $appends = [
        'formatted_saldo',
        'logo_url',
    ];

    protected function logoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value, $attributes) {
                $logo = $attributes['logo'] ?? null;
                if (empty($logo)) {
                    return url('/images/default-logo.png');
                }

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('public');
                return $disk->url($logo);
            },
        );
    }


    public function rollbackSaldo(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            $this->decrement('saldo', $amount, []);
        });
    }

    // Helper method untuk format saldo
    public function getFormattedSaldoAttribute()
    {
        return (string) money($this->saldo, 'IDR');
    }

    public function riwayatSaldo()
    {
        return $this->hasMany(JurnalKeuangan::class, 'referensi_id')
            ->where('kategori', 'Saldo')
            ->where('sub_kategori', 'Tambah Saldo')
            ->orderBy('tanggal', 'desc');
    }
}
