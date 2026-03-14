<?php

namespace App\Models;

use App\Models\User;
use App\Models\Supir;
use App\Enums\TipeNama;
use App\Models\Pekerja;
use App\Models\Penjual;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\{TransaksiOperasional, TransaksiDo};
use App\Observers\JurnalKeuanganObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Traits\{JurnalKeuanganTrait, DokumentasiTrait};

class JurnalKeuangan extends Model
{
    use SoftDeletes, JurnalKeuanganTrait, DokumentasiTrait;

    protected $table = 'jurnal_keuangan';

    protected $fillable = [
        'tanggal',
        'jenis_transaksi',
        'kategori',
        'sub_kategori',
        'nominal',
        'sumber_transaksi',
        'referensi_id',
        'nomor_referensi',
        'pihak_terkait',
        'tipe_pihak',
        'cara_pembayaran',
        'keterangan',
        'perusahaan_id',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
        'saldo_sebelum' => 'decimal:0',
        'saldo_sesudah' => 'decimal:0',
        'mempengaruhi_kas' => 'boolean',
        'tipe_pihak' => TipeNama::class
    ];

    // Tambahkan konstanta di model JurnalKeuangan
    const KATEGORI_TRANSAKSI = [
        'DO' => 'DO',
        'OPERASIONAL' => 'Operasional',
        'SALDO' => 'Saldo'
    ];

    const SUB_KATEGORI_SALDO = [
        'TAMBAH' => 'Tambah Saldo',
        'KOREKSI' => 'Koreksi Saldo'
    ];

    // Relations

    public function transaksiDo()
    {
        return $this->belongsTo(TransaksiDo::class, 'referensi_id')
            ->where('sumber_transaksi', 'DO');
    }
    public function supir(): EloquentBelongsTo
    {
        return $this->belongsTo(Supir::class, 'referensi_id');
    }

    public function pekerja(): EloquentBelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'referensi_id');
    }

    public function penjual(): EloquentBelongsTo
    {
        return $this->belongsTo(Penjual::class, 'referensi_id');
    }

    public function user(): EloquentBelongsTo
    {
        return $this->belongsTo(User::class, 'referensi_id');
    }

    public function operasional(): EloquentBelongsTo
    {
        return $this->belongsTo(TransaksiOperasional::class, 'referensi_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePemasukan($query)
    {
        return $query->where('jenis', 'masuk');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('jenis', 'keluar');
    }

    public function scopeFromDO($query)
    {
        return $query->where('tipe_transaksi', 'transaksi_do');
    }

    public function scopeFromOperasional($query)
    {
        return $query->where('sumber_transaksi', 'Operasional');
    }

    public function scopeAffectsCash($query)
    {
        return $query->where('mempengaruhi_kas', true);
    }

    // Di dalam model LaporanKeuangan

    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getBadgeColorAttribute()
    {
        return $this->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger';
    }

    // Scope untuk filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto sync setelah setiap transaksi kas
        static::created(function ($model) {
            if ($model->mempengaruhi_kas) {
                try {
                    app(JurnalKeuanganObserver::class)->syncSaldoPerusahaan();
                } catch (\Exception $e) {
                    Log::error('Auto-sync error: ' . $e->getMessage());
                }
            }
        });

        // Sync juga untuk update & delete
        static::updated(function ($model) { /* sama seperti created */
        });
        static::deleted(function ($model) { /* sama seperti created */
        });
    }
}
