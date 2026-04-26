<?php

namespace App\Services;

use App\Models\MutasiHutang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DebtService
{
    /**
     * Tambah Hutang (DEBIT)
     */
    public static function increaseDebt(Model $pihak, float $nominal, Model $referensi = null, string $keterangan = null): MutasiHutang
    {
        return self::createMutation($pihak, 'HUTANG_MASUK', $nominal, $referensi, $keterangan);
    }

    /**
     * Kurangi/Bayar Hutang (KREDIT)
     */
    public static function recordPayment(Model $pihak, float $nominal, Model $referensi = null, string $keterangan = null): MutasiHutang
    {
        return self::createMutation($pihak, 'HUTANG_KELUAR', $nominal, $referensi, $keterangan);
    }

    /**
     * Core logic pencatatan mutasi
     */
    private static function createMutation(Model $pihak, string $tipe, float $nominal, ?Model $referensi, ?string $keterangan): MutasiHutang
    {
        return DB::transaction(function () use ($pihak, $tipe, $nominal, $referensi, $keterangan) {
            $currentHutangTotal = (float) ($pihak->hutang ?? 0);
            
            if ($tipe === 'HUTANG_MASUK') {
                $pihak->increment('hutang', $nominal);
            } else {
                // Jangan decrement 'hutang' di database karena 'hutang' adalah Total Akumulasi.
                // Sisa hutang berkurang via record PembayaranHutang.
            }

            return MutasiHutang::create([
                'perusahaan_id' => $pihak->perusahaan_id ?? auth()->user()?->perusahaan_id ?? 1,
                'pihak_id' => $pihak->id,
                'pihak_type' => get_class($pihak),
                'tanggal' => now(),
                'tipe' => $tipe,
                'nominal' => $nominal,
                'saldo_akhir' => $pihak->fresh()->sisa_hutang,
                'referensi_id' => $referensi?->id,
                'referensi_type' => $referensi ? get_class($referensi) : null,
                'keterangan' => $keterangan,
            ]);
        });
    }
}
