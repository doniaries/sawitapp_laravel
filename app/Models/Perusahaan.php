<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'saldo' => 'decimal:0',
        'setting' => 'json',
    ];

    protected function logo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return url('/images/default-logo.png');
                }

                // Debugging: Log full path
                \Log::info('Logo Path Debug', [
                    'original_value' => $value,
                    'storage_url' => Storage::disk('public')->url($value),
                    'full_path' => public_path('storage/' . $value),
                    'file_exists' => file_exists(public_path('storage/' . $value))
                ]);

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