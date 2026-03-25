<?php

namespace App\Actions\Finance;

use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordFinanceTransactionAction
{
    /**
     * Mengeksekusi pencatatan transaksi keuangan.
     * Menggunakan DB::transaction untuk menjamin integritas data (Ledger Snapshot).
     */
    public function execute(array $data)
    {
        return DB::transaction(function () use ($data) {
            $perusahaanId = $data['perusahaan_id'] ?? null;
            if (!$perusahaanId) {
                throw new \Exception("Perusahaan ID wajib disertakan dalam transaksi.");
            }

            // Lock record perusahaan untuk mencegah race condition pada saldo
            $perusahaan = Perusahaan::lockForUpdate()->find($perusahaanId);
            if (!$perusahaan) {
                throw new \Exception("Data perusahaan tidak ditemukan.");
            }

            // Re-fetch and lock the company within the transaction to ensure the latest state
            $perusahaan = Perusahaan::lockForUpdate()->findOrFail($data['perusahaan_id']);

            $saldoAwal = (float) $perusahaan->saldo;
            $nominal = (float) $data['nominal'];
            $jenis = $data['jenis_transaksi']; // Pemasukan / Pengeluaran

            // Update company balance only if it affects cash
            if ($data['mempengaruhi_kas'] ?? true) {
                if ($jenis === 'Pemasukan') {
                    $perusahaan->increment('saldo', $nominal);
                } else {
                    $perusahaan->decrement('saldo', $nominal);
                }
            }

            $saldoAkhir = (float) $perusahaan->fresh()->saldo;

            // Create journal entry with snapshots
            $jurnalData = array_merge($data, [
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'mempengaruhi_kas' => $data['mempengaruhi_kas'] ?? true,
            ]);

            $jurnal = JurnalKeuangan::create($jurnalData);

            Log::info("Transaksi tercatat dengan Snapshot:", [
                'id' => $jurnal->id,
                'awal' => $saldoAwal,
                'akhir' => $saldoAkhir
            ]);

            return $jurnal;
        });
    }
}
