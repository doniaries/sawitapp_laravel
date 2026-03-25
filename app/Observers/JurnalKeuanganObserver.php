<?php

namespace App\Observers;

use Illuminate\Support\Facades\{DB, Log, Cache};
use App\Models\{TransaksiDo, TransaksiOperasional, Perusahaan, JurnalKeuangan};
use Filament\Notifications\Notification;

class JurnalKeuanganObserver
{

    protected $financeAction;

    public function __construct(\App\Actions\Finance\RecordFinanceTransactionAction $financeAction)
    {
        $this->financeAction = $financeAction;
    }

    public function created(JurnalKeuangan $jurnalKeuangan): void
    {
    }

    public function updated(JurnalKeuangan $jurnalKeuangan): void
    {
    }

    public function deleted(JurnalKeuangan $jurnalKeuangan): void
    {
    }

    protected function createLaporan(array $data): void
    {
        try {
            // Pastikan data minimal yang diperlukan
            $requiredFields = [
                'tanggal',
                'jenis_transaksi',
                'kategori',
                'nominal',
                'sub_kategori',
                'sumber_transaksi',
                'referensi_id',
                'nomor_referensi',
                'pihak_terkait',
                'tipe_pihak',
                'cara_pembayaran',
                'keterangan',
            ];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Field {$field} wajib diisi");
                }
            }

            // Set default values jika tidak ada
            $data['sub_kategori'] = $data['sub_kategori'] ?? '-';
            $data['nomor_referensi'] = $data['nomor_referensi'] ?? '-';
            $data['pihak_terkait'] = $data['pihak_terkait'] ?? '-';
            $data['tipe_pihak'] = $data['tipe_pihak'] ?? 'user';
            $data['cara_pembayaran'] = $data['cara_pembayaran'] ?? 'tunai';
            $data['keterangan'] = $data['keterangan'] ?? '-';
            $data['mempengaruhi_kas'] = $data['mempengaruhi_kas'] ?? true;

            // Create laporan via Action (Best Practice)
            $laporan = $this->financeAction->execute($data);

            // Log success dengan informasi minimal
            Log::info('Laporan dibuat: ' . $laporan->id);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleTransaksiDO(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // 1. DELETE existing journals for this DO to prevent duplicates on update
            JurnalKeuangan::where([
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id
            ])->delete();

            if ($transaksiDo->trashed()) {
                DB::commit();
                return;
            }

            $mempengaruhiKas = $transaksiDo->cara_bayar === 'tunai';
            
            // 2. Record GROSS EXPENDITURE (The cost of buying the fruit)
            $this->createLaporan([
                'perusahaan_id' => $transaksiDo->perusahaan_id,
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembelian Buah',
                'nominal' => $transaksiDo->sub_total,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $transaksiDo->penjual?->nama,
                'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                'cara_pembayaran' => $transaksiDo->cara_bayar,
                'keterangan' => "Pembelian DO #{$transaksiDo->nomor} (Bruto)",
                'mempengaruhi_kas' => $mempengaruhiKas
            ]);

            // 3. Record INCOME COMPONENTS (Deductions that return to company)
            
            // A. Upah Bongkar
            if ($transaksiDo->upah_bongkar > 0) {
                $this->createLaporan([
                    'perusahaan_id' => $transaksiDo->perusahaan_id,
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Upah Bongkar',
                    'nominal' => $transaksiDo->upah_bongkar,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Upah Bongkar DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            // B. Biaya Lain
            if ($transaksiDo->biaya_lain > 0) {
                $this->createLaporan([
                    'perusahaan_id' => $transaksiDo->perusahaan_id,
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Biaya Lain',
                    'nominal' => $transaksiDo->biaya_lain,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Biaya Lain DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            // C. Pembayaran Hutang
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->createLaporan([
                    'perusahaan_id' => $transaksiDo->perusahaan_id,
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Potong Hutang',
                    'nominal' => $transaksiDo->pembayaran_hutang,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual?->nama,
                    'tipe_pihak' => \App\Enums\TipeNama::PENJUAL,
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'keterangan' => "Potongan Hutang via DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => $mempengaruhiKas
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error journaling DO:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }


}