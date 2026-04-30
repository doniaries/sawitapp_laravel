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

        // Jika user adalah admin atau superadmin, selalu bisa modifikasi
        // Pastikan method isAdminOrSuperAdmin() ada di model User
        if (method_exists($user, 'isAdminOrSuperAdmin') && $user->isAdminOrSuperAdmin()) {
            return true;
        }

        // Jika tidak, cek apakah hari sudah ditutup
        return !self::isClosed($date, $perusahaanId);
    }
}
