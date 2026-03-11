<?php

namespace App\Observers;

use App\Models\{TransaksiDo, Penjual, Perusahaan, LaporanKeuangan};
use Illuminate\Support\Facades\{DB, Log};
use Filament\Notifications\Notification;

class TransaksiDoObserver
{
    protected $laporanObserver;

    public function __construct(LaporanKeuanganObserver $laporanObserver)
    {
        $this->laporanObserver = $laporanObserver;
    }

    public function creating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            $this->validateRequiredFields($transaksiDo);
            $this->prepareForSave($transaksiDo); // Calculate all amounts

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
        // Generate laporan keuangan
        $this->laporanObserver->handleTransaksiDO($transaksiDo);
    }

    public function updating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Get the original model data
            $oldPembayaranHutang = $transaksiDo->getOriginal('pembayaran_hutang', 0);

            // Rollback old hutang if there was a payment
            if ($oldPembayaranHutang > 0) {
                $transaksiDo->penjual?->increment('hutang', $oldPembayaranHutang);
            }

            // Process new hutang payment if any
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }

            // Validate balance for cash payments
            if ($transaksiDo->cara_bayar === 'tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating TransaksiDo:', [
                'error' => $e->getMessage(),
                'transaksi_id' => $transaksiDo->id,
                'old_payment' => $oldPembayaranHutang,
                'new_payment' => $transaksiDo->pembayaran_hutang
            ]);
            throw $e;
        }
    }

    public function updated(TransaksiDo $transaksiDo)
    {
        // Hapus laporan lama
        LaporanKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->delete();

        // Generate laporan baru
        $this->laporanObserver->handleTransaksiDO($transaksiDo);
    }

    public function deleted(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Kembalikan hutang penjual
            if ($transaksiDo->pembayaran_hutang > 0 && $transaksiDo->penjual) {
                $transaksiDo->penjual->increment('hutang', $transaksiDo->pembayaran_hutang);
            }

            // Hapus laporan keuangan
            LaporanKeuangan::where([
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id
            ])->delete();

            DB::commit();

            $this->sendDeleteNotification($transaksiDo);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Helper Methods
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

        if (!$penjual) {
            throw new \Exception('Data penjual tidak ditemukan');
        }

        if ($transaksiDo->pembayaran_hutang > $penjual->hutang) {
            throw new \Exception(
                "Pembayaran hutang Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') .
                    " melebihi sisa hutang Rp " . number_format($penjual->hutang, 0, ',', '.')
            );
        }

        $penjual->decrement('hutang', $transaksiDo->pembayaran_hutang);
        $transaksiDo->sisa_hutang_penjual = $penjual->fresh()->hutang;
    }

    protected function validateCompanyBalance(TransaksiDo $transaksiDo)
    {
        // Skip validasi untuk data yang dipulihkan
        if ($transaksiDo->exists && $transaksiDo->wasRecentlyCreated === false) {
            return;
        }

        // Validasi saldo hanya untuk transaksi tunai baru
        if ($transaksiDo->cara_bayar === 'tunai') {
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            if ($perusahaan->saldo < 0) {
                throw new \Exception(
                    "Tidak dapat melakukan transaksi tunai karena saldo minus.\n" .
                        "Saldo saat ini: Rp " . number_format($perusahaan->saldo, 0, ',', '.')
                );
            }

            if ($transaksiDo->isClean() && $transaksiDo->sisa_bayar > $perusahaan->saldo) {
                throw new \Exception(
                    "Saldo tidak mencukupi untuk pembayaran tunai.\n" .
                        "Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                        "Dibutuhkan: Rp " . number_format($transaksiDo->sisa_bayar, 0, ',', '.')
                );
            }
        }
    }


    protected function sendDeleteNotification(TransaksiDo $transaksiDo)
    {
        $message = "DO #{$transaksiDo->nomor} telah dibatalkan\n";

        if ($transaksiDo->cara_bayar === 'tunai') {
            $totalPemasukan = $transaksiDo->upah_bongkar +
                $transaksiDo->biaya_lain +
                $transaksiDo->pembayaran_hutang;

            $message .= "\nPerubahan Saldo:";
            if ($totalPemasukan > 0) {
                $message .= "\n- Pembatalan pemasukan: -Rp " .
                    number_format($totalPemasukan, 0, ',', '.');
            }
            if ($transaksiDo->sisa_bayar > 0) {
                $message .= "\n- Pengembalian pengeluaran: +Rp " .
                    number_format($transaksiDo->sisa_bayar, 0, ',', '.');
            }
        }

        if ($transaksiDo->pembayaran_hutang > 0) {
            $message .= "\n\nInfo Hutang:";
            $message .= "\n- Hutang dikembalikan: +Rp " .
                number_format($transaksiDo->pembayaran_hutang, 0, ',', '.');

            if ($transaksiDo->penjual) {
                $message .= "\n- Hutang terkini: Rp " .
                    number_format($transaksiDo->penjual->hutang, 0, ',', '.');
            }
        }

        Notification::make()
            ->title('Transaksi DO Dibatalkan')
            ->warning()
            ->body($message)
            ->send();
    }

    protected function prepareForSave(TransaksiDo $transaksiDo)
    {
        // Generate nomor DO jika baru
        if (!$transaksiDo->nomor) {
            $transaksiDo->nomor = $transaksiDo->generateMonthlyNumber();
        }

        // Set default values
        $transaksiDo->upah_bongkar = $transaksiDo->upah_bongkar ?? 0;
        $transaksiDo->biaya_lain = $transaksiDo->biaya_lain ?? 0;
        $transaksiDo->pembayaran_hutang = $transaksiDo->pembayaran_hutang ?? 0;
        $transaksiDo->cara_bayar = $transaksiDo->cara_bayar ?? 'tunai';

        // Kalkulasi
        $transaksiDo->sub_total = $transaksiDo->tonase * $transaksiDo->harga_satuan;

        $komponenPengurangan =
            $transaksiDo->upah_bongkar +
            $transaksiDo->biaya_lain +
            $transaksiDo->pembayaran_hutang;

        $transaksiDo->sisa_bayar = max(0, $transaksiDo->sub_total - $komponenPengurangan);
    }

    public function restored(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Bypass saldo check untuk transaksi yang dipulihkan
            if ($transaksiDo->cara_bayar === 'tunai') {
                $perusahaan = Perusahaan::lockForUpdate()->first();

                // Increment saldo untuk pembayaran tunai
                $totalPemasukan = $transaksiDo->upah_bongkar +
                    $transaksiDo->biaya_lain +
                    $transaksiDo->pembayaran_hutang;

                if ($totalPemasukan > 0) {
                    $perusahaan->increment('saldo', $totalPemasukan);
                }

                if ($transaksiDo->sisa_bayar > 0) {
                    $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
                }
            }

            // Restore hutang penjual jika ada
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }

            // Generate ulang laporan keuangan
            $this->laporanObserver->handleTransaksiDO($transaksiDo);

            DB::commit();

            Notification::make()
                ->title('Transaksi DO Dipulihkan')
                ->success()
                ->body("DO #{$transaksiDo->nomor} berhasil dipulihkan")
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function forceDeleted(TransaksiDo $transaksiDo)
    {
        // Hapus permanen semua data terkait
        LaporanKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->forceDelete();

        Notification::make()
            ->title('Transaksi DO Dihapus Permanen')
            ->warning()
            ->body("DO #{$transaksiDo->nomor} telah dihapus secara permanen")
            ->send();
    }
}
