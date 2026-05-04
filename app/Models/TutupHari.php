<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutupHari extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'tutup_hari';

    protected $fillable = [
        'perusahaan_id',
        'tanggal',
        'total_do_tonase',
        'total_do_rupiah',
        'total_pemasukan',
        'total_pengeluaran',
        'saldo_akhir_sistem',
        'saldo_akhir_fisik',
        'selisih',
        'catatan',
        'user_id',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_do_tonase' => 'decimal:2',
        'total_do_rupiah' => 'decimal:2',
        'total_pemasukan' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
        'saldo_akhir_sistem' => 'decimal:2',
        'saldo_akhir_fisik' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cek apakah sebuah tanggal sudah ditutup.
     */
    public static function isClosed($date, $perusahaanId): bool
    {
        return self::where('perusahaan_id', $perusahaanId)
            ->where('tanggal', $date)
            ->where('status', 'closed')
            ->exists();
    }

    /**
     * Cek apakah user boleh memodifikasi data pada tanggal tertentu.
     */
    public static function canModify($date, $perusahaanId, $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Jika user adalah superadmin, selalu bisa modifikasi
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Jika tidak, cek apakah hari sudah ditutup
        return !self::isClosed($date, $perusahaanId);
    }

    /**
     * Melakukan proses penutupan hari
     */
    public static function performClosing(array $data, int $perusahaanId): self
    {
        $tanggal = $data['tanggal'];
        
        $totalTonase = TransaksiDo::whereDate('tanggal', $tanggal)->sum('tonase');
        $totalRupiah = TransaksiDo::whereDate('tanggal', $tanggal)->sum('sub_total');
        $totalMasuk = JurnalKeuangan::whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pemasukan')
            ->sum('nominal');
        $totalKeluar = JurnalKeuangan::whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');
        
        $perusahaan = Perusahaan::query()->find($perusahaanId);
        $saldoAwal = $perusahaan?->saldo ?? 0;
        
        $saldoSistem = $saldoAwal + $totalMasuk - $totalKeluar;
        $saldoFisik = (float) ($data['saldo_akhir_fisik'] ?? 0);

        $closing = self::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $tanggal,
            'total_do_tonase' => $totalTonase,
            'total_do_rupiah' => $totalRupiah,
            'total_pemasukan' => $totalMasuk,
            'total_pengeluaran' => $totalKeluar,
            'saldo_akhir_sistem' => $saldoSistem,
            'saldo_akhir_fisik' => $saldoFisik,
            'selisih' => $saldoFisik - $saldoSistem,
            'catatan' => $data['catatan'] ?? null,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'status' => 'closed',
        ]);

        // Update Saldo Perusahaan sesuai uang fisik yang ada
        $closing->perusahaan->update(['saldo' => $saldoFisik]);

        return $closing;
    }
}
