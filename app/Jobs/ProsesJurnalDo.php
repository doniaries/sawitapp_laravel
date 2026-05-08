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

            // 2. Record PURCHASE EXPENDITURE (Pembelian Buah - Net of deductions)
            if ($this->transaksiDo->cara_bayar === 'tunai & transfer') {
                $nominalTunai = (float) $this->transaksiDo->nominal_tunai;
                $nominalTransfer = max(0, (float) $this->transaksiDo->sisa_bayar - $nominalTunai);

                if ($nominalTunai > 0) {
                    $this->createLaporan($financeAction, [
                        'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                        'tanggal' => $this->transaksiDo->tanggal,
                        'jenis_transaksi' => 'Pengeluaran',
                        'kategori' => 'DO',
                        'sub_kategori' => 'Pembelian Buah',
                        'nominal' => $nominalTunai,
                        'sumber_transaksi' => 'DO',
                        'referensi_id' => $this->transaksiDo->id,
                        'nomor_referensi' => $this->transaksiDo->nomor,
                        'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                        'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                        'cara_pembayaran' => 'tunai',
                        'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Bagian Tunai)",
                        'mempengaruhi_kas' => true
                    ]);
                }

                if ($nominalTransfer > 0) {
                    $this->createLaporan($financeAction, [
                        'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                        'tanggal' => $this->transaksiDo->tanggal,
                        'jenis_transaksi' => 'Pengeluaran',
                        'kategori' => 'DO',
                        'sub_kategori' => 'Pembelian Buah',
                        'nominal' => $nominalTransfer,
                        'sumber_transaksi' => 'DO',
                        'referensi_id' => $this->transaksiDo->id,
                        'nomor_referensi' => $this->transaksiDo->nomor,
                        'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                        'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                        'cara_pembayaran' => 'transfer',
                        'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Bagian Transfer)",
                        'mempengaruhi_kas' => false
                    ]);
                }
            } else {
                $mempengaruhiKasUtama = $this->transaksiDo->cara_bayar === 'tunai';
                
                if ($this->transaksiDo->sisa_bayar > 0) {
                    $this->createLaporan($financeAction, [
                        'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                        'tanggal' => $this->transaksiDo->tanggal,
                        'jenis_transaksi' => 'Pengeluaran',
                        'kategori' => 'DO',
                        'sub_kategori' => 'Pembelian Buah',
                        'nominal' => $this->transaksiDo->sisa_bayar,
                        'sumber_transaksi' => 'DO',
                        'referensi_id' => $this->transaksiDo->id,
                        'nomor_referensi' => $this->transaksiDo->nomor,
                        'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                        'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                        'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                        'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor}",
                        'mempengaruhi_kas' => $mempengaruhiKasUtama
                    ]);
                }
            }

            // 3. Record EXPENSE COMPONENTS (Upah & Biaya)
            $mempengaruhiKasBiaya = in_array($this->transaksiDo->cara_bayar, ['tunai', 'tunai & transfer']);

            if ($this->transaksiDo->upah_bongkar > 0) {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Upah Bongkar',
                    'nominal' => $this->transaksiDo->upah_bongkar,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => 'Pekerja Bongkar',
                    'tipe_pihak' => \App\Enums\TipeNama::PEKERJA,
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => "Biaya Upah Bongkar DO #{$this->transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKasBiaya
                ]);
            }

            if ($this->transaksiDo->biaya_lain > 0) {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Biaya Lain',
                    'nominal' => $this->transaksiDo->biaya_lain,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => 'Lain-lain',
                    'tipe_pihak' => \App\Enums\TipeNama::LAINNYA,
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => "Biaya Lain-lain DO #{$this->transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKasBiaya
                ]);
            }

            // 4. Record INCOME COMPONENTS (Potong Hutang - Non Cash Offset)
            if ($this->transaksiDo->pembayaran_hutang > 0) {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Potong Hutang',
                    'nominal' => $this->transaksiDo->pembayaran_hutang,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Hutang via DO #{$this->transaksiDo->nomor}",
                    'mempengaruhi_kas' => false
                ]);
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
