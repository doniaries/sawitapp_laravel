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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\{JurnalKeuanganTrait, DokumentasiTrait, BelongsToTenant};

class JurnalKeuangan extends Model
{
    use SoftDeletes, JurnalKeuanganTrait, DokumentasiTrait, BelongsToTenant;

    protected $table = 'jurnal_keuangan';

    protected $fillable = [
        'tanggal',
        'jenis_transaksi',
        'kategori',
        'sub_kategori',
        'nominal',
        'saldo_awal',
        'saldo_akhir',
        'sumber_transaksi',
        'referensi_id',
        'nomor_referensi',
        'pihak_terkait',
        'tipe_pihak',
        'cara_pembayaran',
        'keterangan',
        'perusahaan_id',
        'mempengaruhi_kas',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
        'saldo_awal' => 'decimal:0',
        'saldo_akhir' => 'decimal:0',
        'saldo_sebelum' => 'decimal:0',
        'saldo_sesudah' => 'decimal:0',
        'mempengaruhi_kas' => 'boolean',
        'tipe_pihak' => TipeNama::class
    ];

    // Tambahkan konstanta di model JurnalKeuangan
    const KATEGORI_TRANSAKSI = [
        'DO' => 'DO',
        'OPERASIONAL' => 'Operasional',
        'SALDO' => 'Saldo',
        'PENGAJUAN' => 'Pengajuan Dana'
    ];

    const SUB_KATEGORI_SALDO = [
        'TAMBAH' => 'Tambah Saldo',
        'KOREKSI' => 'Koreksi Saldo',
        'CAIR_DANA' => 'Pencairan Dana Pengajuan'
    ];

    // Relations

    public function transaksiDo()
    {
        return $this->belongsTo(TransaksiDo::class, 'referensi_id')
            ->where('sumber_transaksi', '=', 'DO', 'and');
    }
    public function supir(): EloquentBelongsTo
    {
        return $this->belongsTo(Supir::class, 'pihak_terkait', 'nama');
    }

    public function pekerja(): EloquentBelongsTo
    {
        return $this->belongsTo(Pekerja::class, 'pihak_terkait', 'nama');
    }

    public function penjual(): EloquentBelongsTo
    {
        return $this->belongsTo(Penjual::class, 'pihak_terkait', 'nama');
    }

    public function user(): EloquentBelongsTo
    {
        return $this->belongsTo(User::class, 'pihak_terkait', 'name');
    }

    public function operasional(): EloquentBelongsTo
    {
        return $this->belongsTo(TransaksiOperasional::class, 'referensi_id');
    }

    public function tambahSaldo(): EloquentBelongsTo
    {
        return $this->belongsTo(TambahSaldo::class, 'referensi_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePemasukan(Builder $query): Builder
    {
        return $query->where('jenis_transaksi', '=', 'Pemasukan', 'and');
    }

    public function scopePengeluaran(Builder $query): Builder
    {
        return $query->where('jenis_transaksi', '=', 'Pengeluaran', 'and');
    }

    public function scopeFromDO(Builder $query): Builder
    {
        return $query->where('sumber_transaksi', '=', 'DO', 'and');
    }

    public function scopeFromOperasional(Builder $query): Builder
    {
        return $query->where('sumber_transaksi', '=', 'Operasional', 'and');
    }

    public function scopeAffectsCash(Builder $query): Builder
    {
        return $query->where('mempengaruhi_kas', '=', true, 'and');
    }

    // Di dalam model JurnalKeuangan

    public function getFormattedNominalAttribute()
    {
        return (string) money($this->nominal, 'IDR');
    }

    public function getBadgeColorAttribute()
    {
        return $this->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger';
    }

    // Scope untuk filtering by date range
    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto sync setelah setiap transaksi kas
        static::created(function ($model) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_stats_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
            \Illuminate\Support\Facades\Cache::forget("dashboard_pie_chart_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
        });

        static::updated(function ($model) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_stats_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
            \Illuminate\Support\Facades\Cache::forget("dashboard_pie_chart_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_stats_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
            \Illuminate\Support\Facades\Cache::forget("dashboard_pie_chart_tenant_{$model->perusahaan_id}_" . now()->format('YmdH'));
        });
    }
}
