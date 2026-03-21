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
    protected $financeAction;

    public function __construct(\App\Actions\Finance\RecordFinanceTransactionAction $financeAction)
    {
        $this->financeAction = $financeAction;
    }

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

            // 1. Create PembayaranHutang record first for history and reference
            $payment = PembayaranHutang::create([
                'tanggal' => $tanggal,
                'nominal' => $nominal,
                'tipe_nama' => $tipePihak,
                'penjual_id' => $tipePihak === 'penjual' ? $pihak->id : null,
                'supir_id' => $tipePihak === 'supir' ? $pihak->id : null,
                'pekerja_id' => $tipePihak === 'pekerja' ? $pihak->id : null,
                'keterangan' => $keterangan ?? "Pelunasan Hutang",
                'perusahaan_id' => $perusahaanId,
            ]);

            // 2. Record Finance Transaction (Jurnal & Update Saldo)
            $this->financeAction->execute([
                'perusahaan_id' => $perusahaanId,
                'tanggal' => $tanggal,
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'Hutang',
                'sub_kategori' => 'Bayar Hutang',
                'nominal' => $nominal,
                'sumber_transaksi' => 'Pembayaran Hutang',
                'referensi_id' => $payment->id, 
                'nomor_referensi' => sprintf('PAY-%s', str_pad((string)$payment->id, 5, '0', STR_PAD_LEFT)),
                'pihak_terkait' => $pihak->nama,
                'tipe_pihak' => $tipePihak,
                'cara_pembayaran' => $caraPembayaran,
                'keterangan' => $keterangan ?? "Terima Pembayaran Hutang: {$pihak->nama}",
                'mempengaruhi_kas' => true,
            ]);

            // 3. Record in Ledger (MutasiHutang) and update balance via DebtService
            DebtService::recordPayment(
                $pihak,
                $nominal,
                $payment,
                $keterangan ?? "Pembayaran Hutang"
            );
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
