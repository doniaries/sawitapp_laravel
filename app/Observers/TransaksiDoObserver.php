<?php

namespace App\Observers;

use App\Models\{TransaksiDo, Penjual, Perusahaan};
use App\Services\DebtService;
use Illuminate\Support\Facades\DB;

class TransaksiDoObserver
{
    public function __construct()
    {
    }

    public function creating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();
            $this->validateRequiredFields($transaksiDo);
            $this->prepareForSave($transaksiDo);
            if ($transaksiDo->cara_bayar === 'tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function created(TransaksiDo $transaksiDo)
    {
        \App\Jobs\ProcessDoJournals::dispatch($transaksiDo);
    }

    public function updating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();
            $oldPembayaranHutang = $transaksiDo->getOriginal('pembayaran_hutang', 0);
            if ($oldPembayaranHutang > 0) {
                $transaksiDo->penjual?->increment('hutang', $oldPembayaranHutang);
            }
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }
            if ($transaksiDo->cara_bayar === 'tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updated(TransaksiDo $transaksiDo)
    {
        \App\Jobs\ProcessDoJournals::dispatch($transaksiDo);
    }

    public function deleted(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();
            DebtService::increaseDebt(
                $transaksiDo->penjual, 
                $transaksiDo->pembayaran_hutang, 
                $transaksiDo, 
                "Pembatalan transaksi DO #{$transaksiDo->nomor}"
            );
            \App\Jobs\ProcessDoJournals::dispatch($transaksiDo);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function validateRequiredFields(TransaksiDo $transaksiDo)
    {
        $required = ['penjual_id', 'tanggal', 'tonase', 'harga_satuan'];
        foreach ($required as $field) {
            if (!$transaksiDo->$field) {
                throw new \Exception("Field {$field} wajib diisi");
            }
        }
    }

    protected function handleHutangPenjual(TransaksiDo $transaksiDo)
    {
        $penjual = Penjual::lockForUpdate()->find($transaksiDo->penjual_id);
        if (!$penjual) throw new \Exception('Data penjual tidak ditemukan');

        if ($transaksiDo->pembayaran_hutang > $penjual->hutang) {
            throw new \Exception("Pembayaran hutang melebihi sisa hutang.");
        }

        DebtService::recordPayment(
            $penjual, 
            $transaksiDo->pembayaran_hutang, 
            $transaksiDo, 
            "Pembayaran hutang via DO #{$transaksiDo->nomor}"
        );
        $transaksiDo->sisa_hutang_penjual = $penjual->fresh()->hutang;
    }

    protected function validateCompanyBalance(TransaksiDo $transaksiDo)
    {
        if (app()->runningInConsole()) return;
        if ($transaksiDo->exists && $transaksiDo->wasRecentlyCreated === false) return;

        $nominalDibutuhkan = match($transaksiDo->cara_bayar) {
            'tunai' => $transaksiDo->sisa_bayar,
            'tunai & transfer' => $transaksiDo->nominal_tunai,
            default => 0
        };

        if ($nominalDibutuhkan > 0) {
            $perusahaan = Perusahaan::lockForUpdate()->find($transaksiDo->perusahaan_id);
            if ($perusahaan->saldo < $nominalDibutuhkan) {
                throw new \Exception("Saldo tidak mencukupi untuk porsi tunai.");
            }
        }
    }

    protected function prepareForSave(TransaksiDo $transaksiDo)
    {
        if (!$transaksiDo->nomor) {
            $transaksiDo->nomor = $transaksiDo->generateMonthlyNumber($transaksiDo->tanggal);
        }
        $transaksiDo->sub_total = $transaksiDo->tonase * $transaksiDo->harga_satuan;
        $komponenPengurangan = ($transaksiDo->upah_bongkar ?? 0) + ($transaksiDo->biaya_lain ?? 0) + ($transaksiDo->pembayaran_hutang ?? 0);
        $transaksiDo->sisa_bayar = max(0, $transaksiDo->sub_total - $komponenPengurangan);
    }

    public function restored(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();
            if ($transaksiDo->cara_bayar === 'tunai') {
                $perusahaan = Perusahaan::lockForUpdate()->first();
                $totalPemasukan = ($transaksiDo->upah_bongkar ?? 0) + ($transaksiDo->biaya_lain ?? 0) + ($transaksiDo->pembayaran_hutang ?? 0);
                if ($totalPemasukan > 0) $perusahaan->increment('saldo', $totalPemasukan);
                if ($transaksiDo->sisa_bayar > 0) $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
            }
            if ($transaksiDo->pembayaran_hutang > 0) $this->handleHutangPenjual($transaksiDo);
            \App\Jobs\ProcessDoJournals::dispatch($transaksiDo);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function forceDeleted(TransaksiDo $transaksiDo)
    {
        \App\Models\JurnalKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->forceDelete();
    }
}
