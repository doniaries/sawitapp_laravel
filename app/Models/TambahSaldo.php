<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class TambahSaldo extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'tambah_saldo';
    protected $fillable = [
        'perusahaan_id',
        'user_id',
        'tanggal',
        'nominal',
        'keterangan',
        'bukti_transfer',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:0',
    ];

    protected static function booted()
    {
        static::created(function ($record) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                // 1. Tambah Saldo Perusahaan
                $perusahaan = Perusahaan::findOrFail($record->perusahaan_id);
                $saldoAwal = $perusahaan->saldo;
                $perusahaan->increment('saldo', $record->nominal);
                $saldoAkhir = $perusahaan->saldo;

                // 2. Catat ke Jurnal Keuangan
                JurnalKeuangan::create([
                    'perusahaan_id' => $record->perusahaan_id,
                    'tanggal' => $record->tanggal ?? now(),
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => JurnalKeuangan::KATEGORI_TRANSAKSI['SALDO'],
                    'sub_kategori' => JurnalKeuangan::SUB_KATEGORI_SALDO['TAMBAH'],
                    'nominal' => $record->nominal,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'referensi_id' => $record->id,
                    'nomor_referensi' => 'TS-' . $record->id,
                    'sumber_transaksi' => 'Tambah Saldo',
                    'tipe_pihak' => \App\Enums\TipeNama::USER,
                    'cara_pembayaran' => 'transfer',
                    'pihak_terkait' => $record->user?->name ?? 'Admin',
                    'keterangan' => 'Top up saldo: ' . $record->keterangan,
                    'mempengaruhi_kas' => true,
                ]);
            });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
}
