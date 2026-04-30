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
            
            // Reversal PembayaranHutang sebelumnya jika ada perubahan nominal
            $oldPembayaranHutang = $transaksiDo->getOriginal('pembayaran_hutang', 0);
            if ($oldPembayaranHutang != $transaksiDo->pembayaran_hutang) {
                \App\Models\PembayaranHutang::query()
                    ->where('referensi_id', $transaksiDo->id)
                    ->where('referensi_type', get_class($transaksiDo))
                    ->forceDelete();

                if ($transaksiDo->pembayaran_hutang > 0) {
                    $this->handleHutangPenjual($transaksiDo);
                }
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
            
            // Hapus record pembayaran hutang yang terkait
            \App\Models\PembayaranHutang::query()
                ->where('referensi_id', $transaksiDo->id)
                ->where('referensi_type', get_class($transaksiDo))
                ->delete();

            // Ledger mutation for cancellation
            DebtService::recordPayment(
                $transaksiDo->penjual, 
                -$transaksiDo->pembayaran_hutang, // Negative nominal for reversal in ledger
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

        if ($transaksiDo->pembayaran_hutang > $penjual->sisa_hutang) {
            throw new \Exception("Pembayaran hutang Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') . " melebihi sisa hutang Rp " . number_format($penjual->sisa_hutang, 0, ',', '.'));
        }

        // 1. Create PembayaranHutang record for the trait calculation
        \App\Models\PembayaranHutang::create([
            'tanggal' => $transaksiDo->tanggal,
            'nominal' => $transaksiDo->pembayaran_hutang,
            'tipe_nama' => 'penjual',
            'penjual_id' => $transaksiDo->penjual_id,
            'keterangan' => "Potong DO #{$transaksiDo->nomor}",
            'perusahaan_id' => $transaksiDo->perusahaan_id,
            'referensi_id' => $transaksiDo->id,
            'referensi_type' => get_class($transaksiDo),
        ]);

        // 2. Record mutation in ledger (no decrement on model anymore)
        DebtService::recordPayment(
            $penjual, 
            $transaksiDo->pembayaran_hutang, 
            $transaksiDo, 
            "Pembayaran hutang via DO #{$transaksiDo->nomor}"
        );
        $transaksiDo->sisa_hutang_penjual = $penjual->fresh()->sisa_hutang;
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
            $user = auth()->user();
            // Jika user adalah Admin/SuperAdmin, abaikan validasi saldo (boleh minus)
            if ($user && method_exists($user, 'isAdminOrSuperAdmin') && $user->isAdminOrSuperAdmin()) {
                return;
            }

            $perusahaan = Perusahaan::lockForUpdate()->find($transaksiDo->perusahaan_id);
            if ($perusahaan && $perusahaan->saldo < $nominalDibutuhkan) {
                throw new \Exception("Saldo perusahaan tidak mencukupi untuk pembayaran tunai (Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "). Transaksi hanya bisa dilanjutkan oleh Admin.");
            }
        }
    }

    protected function prepareForSave(TransaksiDo $transaksiDo)
    {
        if (!$transaksiDo->nomor) {
            $transaksiDo->nomor = $transaksiDo->generateMonthlyNumber($transaksiDo->tanggal);
        }

        // Pastikan tidak ada nilai null untuk field database yang non-nullable
        $transaksiDo->upah_bongkar = $transaksiDo->upah_bongkar ?? 0;
        $transaksiDo->biaya_lain = $transaksiDo->biaya_lain ?? 0;
        $transaksiDo->pembayaran_hutang = $transaksiDo->pembayaran_hutang ?? 0;
        $transaksiDo->nominal_tunai = $transaksiDo->nominal_tunai ?? 0;

        $transaksiDo->sub_total = $transaksiDo->tonase * $transaksiDo->harga_satuan;
        $komponenPengurangan = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang;
        $transaksiDo->sisa_bayar = max(0, $transaksiDo->sub_total - $komponenPengurangan);
    }

    public function restored(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();
            if ($transaksiDo->cara_bayar === 'tunai') {
                $perusahaan = Perusahaan::lockForUpdate()->first();
                $totalPemasukan = ($transaksiDo->upah_bongkar ?? 0) + ($transaksiDo->biaya_lain ?? 0) + ($transaksiDo->pembayaran_hutang ?? 0);
                
                // Saldo tetap diupdate saat restore
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
        \App\Models\JurnalKeuangan::query()
            ->where('sumber_transaksi', 'DO')
            ->where('referensi_id', $transaksiDo->id)
            ->forceDelete();
    }
}
