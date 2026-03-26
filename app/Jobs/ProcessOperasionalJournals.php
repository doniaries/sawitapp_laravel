<?php

namespace App\Jobs;

use App\Models\TransaksiOperasional;
use App\Models\JurnalKeuangan;
use App\Actions\Finance\RecordFinanceTransactionAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOperasionalJournals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $operasional;

    /**
     * Create a new job instance.
     */
    public function __construct(TransaksiOperasional $operasional)
    {
        $this->operasional = $operasional;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordFinanceTransactionAction $financeAction): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus laporan keuangan yang lama
            JurnalKeuangan::where([
                'kategori' => 'Operasional',
                'referensi_id' => $this->operasional->id
            ])->delete();

            if ($this->operasional->trashed()) {
                DB::commit();
                return;
            }

            // 2. Resolve target type slug
            $tipePihak = match ($this->operasional->pihak_type) {
                \App\Models\Penjual::class => 'penjual',
                \App\Models\Supir::class => 'supir',
                \App\Models\Pekerja::class => 'pekerja',
                \App\Models\User::class => 'user',
                default => 'user',
            };

            // 3. Buat laporan via Action
            $financeAction->execute([
                'perusahaan_id' => $this->operasional->perusahaan_id,
                'tanggal' => $this->operasional->tanggal,
                'jenis_transaksi' => ucfirst($this->operasional->operasional), 
                'kategori' => 'Operasional',
                'sub_kategori' => $this->operasional->kategori?->label() ?? '-',
                'nominal' => $this->operasional->nominal,
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $this->operasional->id,
                'nomor_referensi' => sprintf('OP-%s', str_pad($this->operasional->id, 5, '0', STR_PAD_LEFT)),
                'pihak_terkait' => $this->operasional->nama,
                'tipe_pihak' => $tipePihak,
                'cara_pembayaran' => $this->operasional->cara_pembayaran ?? 'tunai',
                'keterangan' => $this->operasional->keterangan ?: '-',
                'mempengaruhi_kas' => true,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job ProcessOperasionalJournals Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
