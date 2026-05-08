<?php

namespace App\Jobs;

use App\Models\TransaksiDo;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Actions\Finance\RecordFinanceTransactionAction;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProsesJurnalDo
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TransaksiDo $transaksiDo;

    /**
     * Create a new job instance.
     */
    public function __construct(TransaksiDo $transaksiDo)
    {
        $this->transaksiDo = $transaksiDo;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordFinanceTransactionAction $financeAction): void
    {
        try {
            DB::beginTransaction();

            // 1. REVERSE balance and DELETE existing journals for this DO
            $existingJournals = JurnalKeuangan::where('sumber_transaksi', '=', 'DO', 'and')
                ->where('referensi_id', '=', $this->transaksiDo->id, 'and')
                ->get();

            foreach ($existingJournals as $journal) {
                if ($journal->mempengaruhi_kas) {
                    /** @var Perusahaan $perusahaan */
                    $perusahaan = Perusahaan::query()->find($journal->perusahaan_id);
                    if ($perusahaan) {
                        if ($journal->jenis_transaksi === 'Pemasukan') {
                            $perusahaan->decrement('saldo', $journal->nominal, []);
                        } else {
                            $perusahaan->increment('saldo', $journal->nominal, []);
                        }
                    }
                }
                $journal->forceDelete();
            }

            if ($this->transaksiDo->trashed()) {
                DB::commit();
                return;
            }

            $perusahaan = Perusahaan::query()->find($this->transaksiDo->perusahaan_id);
            if (!$perusahaan) {
                throw new \Exception("Perusahaan tidak ditemukan.");
            }

            // 1. Jurnal Utama: Nilai Pembelian Buah (BRUTO)
            // Hanya untuk record laporan, TIDAK mempengaruhi saldo kas langsung
            // karena rincian pembayarannya (tunai/transfer/hutang) dicatat terpisah di bawah.
            $subtotalBruto = (float)$this->transaksiDo->sub_total;
            $upahBongkar = (float)$this->transaksiDo->upah_bongkar;
            $biayaLain = (float)$this->transaksiDo->biaya_lain;
            $pembayaranHutang = (float)$this->transaksiDo->pembayaran_hutang;
            $caraBayar = $this->transaksiDo->cara_bayar;
            $sisaBayar = (float)$this->transaksiDo->sisa_bayar;
            $nominalTunai = (float)($this->transaksiDo->nominal_tunai ?? 0);

            if ($subtotalBruto > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pembelian Buah',
                    'nominal' => $subtotalBruto,
                    'mempengaruhi_kas' => false, // Record saja
                    'cara_pembayaran' => $caraBayar,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Total Pembelian DO #{$this->transaksiDo->nomor} (Bruto)",
                ]);
            }

            // 2. Jurnal Pembayaran Tunai ke Penjual (Jika ada uang keluar dari laci untuk bayar buah)
            $cashToSeller = 0;
            if ($caraBayar === 'tunai') {
                $cashToSeller = $sisaBayar;
            } elseif ($caraBayar === 'tunai & transfer') {
                $cashToSeller = $nominalTunai;
            }

            if ($cashToSeller > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Bayar Tunai ke Penjual',
                    'nominal' => $cashToSeller,
                    'mempengaruhi_kas' => true,
                    'cara_pembayaran' => 'tunai',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Pembayaran Tunai DO #{$this->transaksiDo->nomor}",
                ]);
                $perusahaan->decrement('saldo', $cashToSeller);
            }

            // 3. Jurnal Potongan Hutang (Record saja, TIDAK menambah saldo kas laci secara fisik)
            if ($pembayaranHutang > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Potongan Hutang',
                    'nominal' => $pembayaranHutang,
                    'mempengaruhi_kas' => false, // Record piutang saja, bukan uang masuk laci
                    'cara_pembayaran' => 'tunai',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Pemasukan dari Potongan Hutang DO #{$this->transaksiDo->nomor}",
                ]);
                // Tidak ada increment saldo di sini
            }

            // 4. Jurnal Biaya Operasional (Pengeluaran): Memotong saldo kas laci
            if ($upahBongkar > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Upah Bongkar',
                    'nominal' => $upahBongkar,
                    'mempengaruhi_kas' => true,
                    'cara_pembayaran' => 'tunai',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Biaya Bongkar DO #{$this->transaksiDo->nomor}",
                ]);
                $perusahaan->decrement('saldo', $upahBongkar);
            }

            if ($biayaLain > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Biaya Lain',
                    'nominal' => $biayaLain,
                    'mempengaruhi_kas' => true,
                    'cara_pembayaran' => 'tunai',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Biaya Lain DO #{$this->transaksiDo->nomor}",
                ]);
                $perusahaan->decrement('saldo', $biayaLain);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job ProsesJurnalDo Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function createLaporan(RecordFinanceTransactionAction $action, array $data): void
    {
        $action->execute($data);
    }
}
