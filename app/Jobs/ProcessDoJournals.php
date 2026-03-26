<?php

namespace App\Jobs;

use App\Models\TransaksiDo;
use App\Models\JurnalKeuangan;
use App\Actions\Finance\RecordFinanceTransactionAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDoJournals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaksiDo;

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

            // 1. DELETE existing journals for this DO
            JurnalKeuangan::where([
                'sumber_transaksi' => 'DO',
                'referensi_id' => $this->transaksiDo->id
            ])->delete();

            if ($this->transaksiDo->trashed()) {
                DB::commit();
                return;
            }

            $mempengaruhiKas = $this->transaksiDo->cara_bayar === 'tunai';
            
            // 2. Record GROSS EXPENDITURE
            if ($this->transaksiDo->cara_bayar === 'tunai & transfer') {
                $nominalTunai = (float) $this->transaksiDo->nominal_tunai;
                $nominalTransfer = $this->transaksiDo->sub_total - $nominalTunai;

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
                        'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Bruto - Bagian Tunai)",
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
                        'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Bruto - Bagian Transfer)",
                        'mempengaruhi_kas' => false
                    ]);
                }
            } else {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pembelian Buah',
                    'nominal' => $this->transaksiDo->sub_total,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                    'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Bruto)",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            // 3. Record INCOME COMPONENTS (Deductions)
            if ($this->transaksiDo->upah_bongkar > 0) {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Upah Bongkar',
                    'nominal' => $this->transaksiDo->upah_bongkar,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Upah Bongkar DO #{$this->transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            if ($this->transaksiDo->biaya_lain > 0) {
                $this->createLaporan($financeAction, [
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Biaya Lain',
                    'nominal' => $this->transaksiDo->biaya_lain,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Biaya Lain DO #{$this->transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

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
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job ProcessDoJournals Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function createLaporan(RecordFinanceTransactionAction $action, array $data): void
    {
        $action->execute($data);
    }
}
