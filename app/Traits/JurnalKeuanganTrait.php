<?php

namespace App\Traits;

use App\Models\{JurnalKeuangan, Perusahaan};
use Illuminate\Support\Facades\{DB, Log};

/**
 * @property float $tonase
 * @property float $harga_satuan
 * @property float $upah_bongkar
 * @property float $biaya_lain
 * @property float $pembayaran_hutang
 * @property float $hutang_awal
 * @property string $cara_bayar
 * @property string $nomor
 * @property int $id
 */
trait JurnalKeuanganTrait
{
    /**
     * Hitung total transaksi
     */
    protected function hitungTotal(): float
    {
        return $this->tonase * $this->harga_satuan;
    }

    /**
     * Hitung total pemasukan dari transaksi
     */
    protected function hitungTotalPemasukan(): float
    {
        return $this->upah_bongkar + $this->biaya_lain + $this->pembayaran_hutang;
    }

    /**
     * Hitung sisa pembayaran
     */
    protected function hitungSisaBayar(): float
    {
        $total = $this->hitungTotal();
        $totalPemasukan = $this->hitungTotalPemasukan();
        return max(0, $total - $totalPemasukan);
    }

    /**
     * Hitung sisa hutang
     */
    protected function hitungSisaHutang(): float
    {
        return max(0, $this->hutang_awal - $this->pembayaran_hutang);
    }

    /**
     * Format currency
     */
    protected function formatCurrency(float $nominal): string
    {
        return (string) money($nominal, 'IDR');
    }

    /**
     * Check transaksi tunai
     */
    protected function isTransaksitunai(): bool
    {
        return $this->cara_bayar === 'tunai';
    }

    /**
     * Log activity
     */
    protected function logTransactionActivity(string $action, array $data): void
    {
        Log::info("Transaction {$action}:", array_merge(
            ['nomor' => $this->nomor ?? '-'],
            $data
        ));
    }
}
