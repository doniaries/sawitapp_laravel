<?php

namespace App\Observers;

use App\Models\TambahSaldo;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Enums\TipeNama;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TambahSaldoObserver
{
    /**
     * Handle the TambahSaldo "created" event.
     */
    public function created(TambahSaldo $tambahSaldo): void
    {
        $this->prosesSaldoDanJurnal($tambahSaldo);
    }

    /**
     * Proses update saldo perusahaan dan pencatatan jurnal keuangan.
     */
    protected function prosesSaldoDanJurnal(TambahSaldo $tambahSaldo): void
    {
        DB::transaction(function () use ($tambahSaldo) {
            $perusahaan = Perusahaan::find($tambahSaldo->perusahaan_id);
            if (!$perusahaan) {
                Log::error("TambahSaldoObserver: Perusahaan ID {$tambahSaldo->perusahaan_id} tidak ditemukan.");
                return;
            }

            $saldoAwal = $perusahaan->saldo;
            $perusahaan->increment('saldo', $tambahSaldo->nominal);
            $saldoAkhir = $perusahaan->saldo;

            // Catat di Jurnal Keuangan
            JurnalKeuangan::create([
                'perusahaan_id' => $tambahSaldo->perusahaan_id,
                'tanggal' => $tambahSaldo->tanggal ?? now(),
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => JurnalKeuangan::KATEGORI_TRANSAKSI['SALDO'],
                'sub_kategori' => JurnalKeuangan::SUB_KATEGORI_SALDO['TAMBAH'],
                'nominal' => $tambahSaldo->nominal,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'sumber_transaksi' => 'Tambah Saldo',
                'referensi_id' => $tambahSaldo->id,
                'nomor_referensi' => 'TS-' . $tambahSaldo->id,
                'pihak_terkait' => $tambahSaldo->user?->name ?? 'Admin',
                'tipe_pihak' => \App\Enums\TipeNama::USER,
                'cara_pembayaran' => 'transfer',
                'keterangan' => 'Top up saldo: ' . $tambahSaldo->keterangan,
                'mempengaruhi_kas' => true,
            ]);
            
            Log::info("TambahSaldoObserver: Saldo perusahaan {$perusahaan->name} berhasil diperbarui (ID: {$tambahSaldo->id})");
        });
    }
}
