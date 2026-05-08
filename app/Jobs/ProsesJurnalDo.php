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

            // 1. Jurnal Utama: Nilai Pembelian Buah (Netto Biaya) sebagai Pengeluaran
            // Kita kurangi dengan biaya agar tidak double counting saat biaya dicatat terpisah
            $upahBongkar = (float)$this->transaksiDo->upah_bongkar;
            $biayaLain = (float)$this->transaksiDo->biaya_lain;
            $subtotalBuahMurni = (float)$this->transaksiDo->sub_total - $upahBongkar - $biayaLain;

            JurnalKeuangan::create([
                'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                'tanggal' => $this->transaksiDo->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembelian Buah',
                'nominal' => $subtotalBuahMurni,
                'mempengaruhi_kas' => true,
                'cara_pembayaran' => $this->transaksiDo->cara_bayar,
                'nomor_referensi' => $this->transaksiDo->nomor,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $this->transaksiDo->id,
                'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                'tipe_pihak' => 'penjual',
                'keterangan' => "Pembelian DO #{$this->transaksiDo->nomor} (Nilai Buah)",
            ]);

            // 2. Jurnal Transfer (Pemasukan): Jika dibayar transfer, admin mengganti uang kas
            $sisaBayar = (float)$this->transaksiDo->sisa_bayar;
            if ($this->transaksiDo->cara_bayar === 'transfer' && $sisaBayar > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Reimbursement Admin',
                    'nominal' => $sisaBayar,
                    'mempengaruhi_kas' => true,
                    'cara_pembayaran' => 'transfer',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => 'Admin',
                    'tipe_pihak' => 'lainnya',
                    'keterangan' => "Masuk Saldo dari Admin (Transfer DO #{$this->transaksiDo->nomor})",
                ]);
            }

            // 3. Jurnal Potongan Hutang (Pemasukan): Hutang penjual yang dipotong masuk ke kas
            $pembayaranHutang = (float)$this->transaksiDo->pembayaran_hutang;
            if ($pembayaranHutang > 0) {
                JurnalKeuangan::create([
                    'perusahaan_id' => $this->transaksiDo->perusahaan_id,
                    'tanggal' => $this->transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Potongan Hutang',
                    'nominal' => $pembayaranHutang,
                    'mempengaruhi_kas' => true,
                    'cara_pembayaran' => 'tunai',
                    'nomor_referensi' => $this->transaksiDo->nomor,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $this->transaksiDo->id,
                    'pihak_terkait' => $this->transaksiDo->penjual?->nama ?? '-',
                    'tipe_pihak' => 'penjual',
                    'keterangan' => "Pemasukan dari Potongan Hutang DO #{$this->transaksiDo->nomor}",
                ]);
            }

            // 4. Jurnal Biaya Operasional (Pengeluaran)
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
