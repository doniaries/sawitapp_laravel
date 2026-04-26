<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToTenant;

class Pekerja extends Model
{
    use SoftDeletes, BelongsToTenant, \App\Traits\HasHutangTrait;
    protected $table = 'pekerja';

    protected $fillable = [
        'id',
        'nama',
        'alamat',
        'telepon',
        'pendapatan',
        'hutang',
        'perusahaan_id',
        'slug',
    ];

    protected $casts = [
        'pendapatan' => 'decimal:0',
        'hutang' => 'decimal:0',
    ];

    public function operasional()
    {
        return $this->hasMany(TransaksiOperasional::class, 'pihak_id')
            ->where('pihak_type', self::class);
    }

    public function jurnalKeuangan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JurnalKeuangan::class, 'pihak_terkait', 'nama')
            ->orderBy('tanggal', 'desc');
    }
}
