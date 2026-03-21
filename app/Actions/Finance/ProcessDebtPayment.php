<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Models\PembayaranHutang;
use App\Models\TransaksiOperasional;
use App\Models\MutasiHutang;
use App\Models\JurnalKeuangan;
use App\Enums\KategoriOperasional;
use App\Enums\TipeNama;
use App\Services\DebtService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProcessDebtPayment
{
    public function execute(
        Model $pihak,
        float $nominal,
        string $tanggal,
        string $caraPembayaran = 'tunai',
        ?string $keterangan = null
    ): void {
        DB::transaction(function () use ($pihak, $nominal, $tanggal, $caraPembayaran, $keterangan) {
            $perusahaanId = $pihak->perusahaan_id;
            $tipePihak = $this->resolveTipePihak($pihak);

            // 1. Create Operational Transaction (Pemasukan)
            $op = TransaksiOperasional::create([
                'tanggal' => $tanggal,
                'operasional' => 'pemasukan',
                'kategori' => KategoriOperasional::BAYAR_HUTANG,
                'tipe_nama' => $tipePihak,
                'pihak_id' => $pihak->id,
                'pihak_type' => get_class($pihak),
                'nominal' => $nominal,
                'keterangan' => $keterangan ?? "Pembayaran Hutang oleh {$pihak->nama}",
                'perusahaan_id' => $perusahaanId,
            ]);

            // 2. Create PembayaranHutang record
            $payment = PembayaranHutang::create([
                'tanggal' => $tanggal,
                'nominal' => $nominal,
                'tipe_nama' => $tipePihak,
                'penjual_id' => $tipePihak === 'penjual' ? $pihak->id : null,
                'supir_id' => $tipePihak === 'supir' ? $pihak->id : null,
                'pekerja_id' => $tipePihak === 'pekerja' ? $pihak->id : null,
                'operasional_id' => $op->id,
                'keterangan' => $keterangan ?? "Pelunasan Hutang",
                'perusahaan_id' => $perusahaanId,
            ]);

            // 3. Record in Ledger (MutasiHutang) and update balance via DebtService
            DebtService::recordPayment(
                $pihak,
                $nominal,
                $payment,
                $keterangan ?? "Pembayaran Hutang"
            );

            // 4. Record in Company Journal (JurnalKeuangan)
            JurnalKeuangan::create([
                'tanggal' => $tanggal,
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'Operasional',
                'sub_kategori' => 'Bayar Hutang',
                'nominal' => $nominal,
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $op->id,
                'pihak_terkait' => $pihak->nama,
                'tipe_pihak' => $tipePihak,
                'cara_pembayaran' => $caraPembayaran,
                'keterangan' => $keterangan ?? "Terima Pembayaran Hutang: {$pihak->nama}",
                'mempengaruhi_kas' => true,
                'perusahaan_id' => $perusahaanId,
            ]);
        });
    }

    private function resolveTipePihak(Model $pihak): string
    {
        return match (get_class($pihak)) {
            \App\Models\Penjual::class => 'penjual',
            \App\Models\Supir::class => 'supir',
            \App\Models\Pekerja::class => 'pekerja',
            default => 'user',
        };
    }
}
