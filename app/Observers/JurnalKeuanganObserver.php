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
        $this->syncSaldoPerusahaan($jurnalKeuangan->perusahaan_id);
    }

    public function updated(JurnalKeuangan $jurnalKeuangan): void
    {
        $this->syncSaldoPerusahaan($jurnalKeuangan->perusahaan_id);
    }

    public function deleted(JurnalKeuangan $jurnalKeuangan): void
    {
        $this->syncSaldoPerusahaan($jurnalKeuangan->perusahaan_id);
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
                'mempengaruhi_kas' => true
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
                    'mempengaruhi_kas' => true
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
                    'mempengaruhi_kas' => true
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
                    'mempengaruhi_kas' => true
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error journaling DO:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function syncSaldoPerusahaan(int $perusahaanId): void
    {
        try {
            DB::beginTransaction();

            $perusahaan = Perusahaan::lockForUpdate()->findOrFail($perusahaanId);

            // Calculate based on TransaksiDoStatWidget logic

            // 1. Get incoming funds from transaksi_do
            $incomingFunds = DB::table('transaksi_do')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->select([
                    DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                    DB::raw('COALESCE(SUM(CASE
                        WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                        THEN sisa_bayar
                        ELSE 0
                    END), 0) as remaining_payments')
                ])->first();

            // 2. Get operational income
            $operationalIncome = DB::table('transaksi_operasional')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->where('operasional', 'pemasukan')
                ->sum('nominal');

            // 3. Get approved Pengajuan Dana income
            $pengajuanIncome = DB::table('pengajuan_dana')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->where('status', 'disetujui')
                ->sum('nominal');

            // 4. Get standalone debt payments (not via DO)
            $standaloneDebtIncome = DB::table('pembayaran_hutang')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->sum('nominal');

            // Total Income components
            $pembayaranHutang = $incomingFunds->total_debt_payments; 
            $pembayaranSisa = $incomingFunds->remaining_payments;    
            $pemasukanOperasional = $operationalIncome;             
            $pemasukanPengajuan = $pengajuanIncome;
            $pemasukanHutangMandiri = $standaloneDebtIncome;

            // Total Income
            $totalPemasukan = $pembayaranHutang + $pembayaranSisa + $pemasukanOperasional + $pemasukanPengajuan + $pemasukanHutangMandiri;

            // Calculate expenditure
            // 1. Total DO expenses
            $pengeluaranDO = DB::table('transaksi_do')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->sum('sub_total'); 

            // 2. Total operational expenses
            $pengeluaranOperasional = DB::table('transaksi_operasional')
                ->where('perusahaan_id', $perusahaanId)
                ->whereNull('deleted_at')
                ->where('operasional', 'pengeluaran')
                ->sum('nominal'); 

            // Total Expenditure
            $totalPengeluaran = $pengeluaranDO + $pengeluaranOperasional;

            // Final Balance
            $saldoAkhir = $totalPemasukan - $totalPengeluaran;

            // Update saldo
            $perusahaan->update(['saldo' => $saldoAkhir]);

            // Log detail untuk tracking
            Log::info('Sync Saldo:', [
                'pemasukan_operasional' => $pemasukanOperasional,
                'pembayaran_hutang' => $pembayaranHutang,
                'pembayaran_sisa' => $pembayaranSisa,
                'total_masuk' => $totalPemasukan,
                'pengeluaran_do' => $pengeluaranDO,
                'pengeluaran_operasional' => $pengeluaranOperasional,
                'total_keluar' => $totalPengeluaran,
                'saldo_akhir' => $saldoAkhir
            ]);

            DB::commit();

            Notification::make()
                ->title('Saldo Berhasil Disinkronkan')
                ->body(sprintf(
                    "Saldo akhir: Rp %s\n" .
                        "Total Masuk: Rp %s\n" .
                        "Total Keluar: Rp %s",
                    number_format($saldoAkhir, 0, ',', '.'),
                    number_format($totalPemasukan, 0, ',', '.'),
                    number_format($totalPengeluaran, 0, ',', '.')
                ))
                ->success()
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sync saldo: ' . $e->getMessage());
            throw $e;
        }
    }
}