<?php

namespace App\Observers;

use Illuminate\Support\Facades\{DB, Log, Cache};
use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Filament\Notifications\Notification;

class LaporanKeuanganObserver
{


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
            $data['tipe_pihak'] = $data['tipe_pihak'] ?? '-';
            $data['cara_pembayaran'] = $data['cara_pembayaran'] ?? 'tunai';
            $data['keterangan'] = $data['keterangan'] ?? '-';
            $data['mempengaruhi_kas'] = $data['mempengaruhi_kas'] ?? true;

            // Create laporan
            $laporan = LaporanKeuangan::create($data);

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

            // Lock perusahaan untuk update
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Handle deleted transactions
            if ($transaksiDo->trashed()) {
                if ($transaksiDo->cara_bayar === 'tunai') {
                    $this->createLaporan([
                        'tanggal' => $transaksiDo->tanggal,
                        'jenis_transaksi' => 'Pengeluaran',
                        'kategori' => 'DO',
                        'sub_kategori' => 'Pembayaran DO',
                        'nominal' => $transaksiDo->sisa_bayar,
                        'sumber_transaksi' => 'DO',
                        'referensi_id' => $transaksiDo->id,
                        'nomor_referensi' => $transaksiDo->nomor,
                        'pihak_terkait' => $transaksiDo->penjual->nama,
                        'tipe_pihak' => 'penjual',
                        'cara_pembayaran' => 'tunai',
                        'keterangan' => "Pembatalan DO #{$transaksiDo->nomor}",
                        'mempengaruhi_kas' => false
                    ]);
                }
                DB::commit();
                return;
            }

            // Handle normal transactions
            if ($transaksiDo->cara_bayar === 'tunai') {
                $perusahaan->increment('saldo', $transaksiDo->total);

                $this->createLaporan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pembayaran DO',
                    'nominal' => $transaksiDo->total,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual', // Add this line
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => "Pembayaran DO Tunai #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => true
                ]);

                Log::info('Transaksi DO Tunai:', [
                    'nomor' => $transaksiDo->nomor,
                    'total' => $transaksiDo->total,
                    'saldo_akhir' => $perusahaan->fresh()->saldo
                ]);
            } else {
                $this->createLaporan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pembayaran DO',
                    'nominal' => $transaksiDo->total,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual', // Add this line
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'keterangan' => "Pembayaran DO Non-Tunai #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => false
                ]);

                Log::info('Transaksi DO Non-Tunai:', [
                    'nomor' => $transaksiDo->nomor,
                    'total' => $transaksiDo->total,
                    'cara_bayar' => $transaksiDo->cara_bayar
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error transaksi DO:', [
                'error' => $e->getMessage(),
                'transaksi' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }

    protected function handleTransaksiNontunai(TransaksiDo $transaksiDo)
    {
        // Catat komponen pemasukan
        $komponenPemasukan = [
            ['sub_kategori' => 'Upah Bongkar', 'nominal' => $transaksiDo->upah_bongkar],
            ['sub_kategori' => 'Biaya Lain', 'nominal' => $transaksiDo->biaya_lain],
            ['sub_kategori' => 'Bayar Hutang', 'nominal' => $transaksiDo->pembayaran_hutang],
        ];

        $totalPemasukan = 0;
        $pihakTerkait = $transaksiDo->penjual ? $transaksiDo->penjual->nama : 'Penjual tidak ditemukan';

        // Catat setiap komponen pemasukan
        foreach ($komponenPemasukan as $komponen) {
            if ($komponen['nominal'] > 0) {
                $this->createLaporan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => $komponen['sub_kategori'],
                    'nominal' => $komponen['nominal'],
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => 'nontunai',
                    'keterangan' => "Pemasukan nontunai DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => false,
                ]);

                $totalPemasukan += $komponen['nominal'];
            }
        }

        // Catat transaksi non tunai untuk sisa bayar
        if ($transaksiDo->sisa_bayar > 0) {
            $this->createLaporan([
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembayaran DO',
                'nominal' => $transaksiDo->sisa_bayar,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $pihakTerkait,
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => $transaksiDo->cara_bayar,
                'keterangan' => "Pembayaran DO via {$transaksiDo->cara_bayar}",
                'mempengaruhi_kas' => false,
            ]);
        }

        Log::info("DO non-tunai #{$transaksiDo->nomor} selesai: +{$totalPemasukan}");
    }

    // di LaporanKeuanganObserver.php

    public function syncSaldoPerusahaan(): void
    {
        try {
            DB::beginTransaction();

            $perusahaan = Perusahaan::lockForUpdate()->firstOrFail();

            // Calculate based on TransaksiDoStatWidget logic

            // 1. Get incoming funds from transaksi_do
            $incomingFunds = DB::table('transaksi_do')
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
            $operationalIncome = DB::table('operasional')
                ->whereNull('deleted_at')
                ->where('operasional', 'pemasukan')
                ->sum('nominal');

            // Total Income components
            $pembayaranHutang = $incomingFunds->total_debt_payments; // Rp 2,000,000
            $pembayaranSisa = $incomingFunds->remaining_payments;    // Rp 164,128,680
            $pemasukanOperasional = $operationalIncome;             // Rp 265,647,000

            // Total Income (Rp 431,775,680)
            $totalPemasukan = $pembayaranHutang + $pembayaranSisa + $pemasukanOperasional;

            // Calculate expenditure
            // 1. Total DO expenses
            $pengeluaranDO = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->sum('sub_total'); // Rp 296,694,980

            // 2. Total operational expenses
            $pengeluaranOperasional = DB::table('operasional')
                ->whereNull('deleted_at')
                ->where('operasional', 'pengeluaran')
                ->sum('nominal'); // Rp 204,000

            // Total Expenditure (Rp 296,898,980)
            $totalPengeluaran = $pengeluaranDO + $pengeluaranOperasional;

            // Final Balance (Rp 134,876,700)
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