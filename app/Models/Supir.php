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
    use HasFactory, SoftDeletes, BelongsToTenant, \App\Traits\HasHutangTrait;

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
}
