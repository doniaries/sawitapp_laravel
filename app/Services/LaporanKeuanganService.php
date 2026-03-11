<?php

namespace App\Services;

use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class LaporanKeuanganService
{
    public function getLaporanData($startDate, $endDate)
    {
        try {
            // 1. Get current data perusahaan
            $perusahaan = Perusahaan::firstOrFail();

            // 2. Get all transactions in date range with proper eager loading
            $laporan = LaporanKeuangan::with(['transaksiDo', 'operasional'])
                ->whereBetween('tanggal', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->get();

            // 3. Calculate totals correctly
            $pemasukan = $laporan->where('jenis_transaksi', 'Pemasukan')->sum('nominal');
            $pengeluaran = $laporan->where('jenis_transaksi', 'Pengeluaran')->sum('nominal');

            // Breakdown by categories
            $pembaranHutang = $laporan->where('sub_kategori', 'Bayar Hutang')->sum('nominal');
            $pemasukkanSisa = $laporan->where('sub_kategori', 'Sisa')->sum('nominal');

            // DO Calculations
            $totalDO = $laporan->where('kategori', 'DO')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->sum('nominal');

            // Operational Calculations
            $totalOperasional = $laporan->where('kategori', 'Operasional')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->sum('nominal');

            // Payment Method Breakdown
            $pembayaran = [
                'tunai' => $laporan->where('cara_pembayaran', 'tunai')->sum('sisa_bayar'),
                'transfer' => $laporan->where('cara_pembayaran', 'transfer')->sum('sisa_bayar'),
                'cair_diluar' => $laporan->where('cara_pembayaran', 'cair di luar')->sum('sisa_bayar'),
                'belum_dibayar' => $laporan->where('cara_pembayaran', 'belum dibayar')->sum('sisa_bayar'),
                'total' => $laporan->sum('sisa_bayar')
            ];

            // Calculate final totals
            $totalPemasukan = $pemasukan;
            $totalPengeluaran = $pengeluaran;

            // Log calculations for debugging
            Log::info('Perhitungan Laporan:', [
                'range' => "$startDate - $endDate",
                'pemasukan' => $totalPemasukan,
                'pengeluaran' => $totalPengeluaran,
                'pembayaran_hutang' => $pembaranHutang,
                'total_do' => $totalDO,
                'total_operasional' => $totalOperasional,
                'breakdown_pembayaran' => $pembayaran
            ]);

            return [
                'perusahaan' => $perusahaan,
                'transaksi' => $laporan,
                'totalPemasukan' => $totalPemasukan,
                'totalPengeluaran' => $totalPengeluaran,
                'pembayaranHutang' => $pembaranHutang,
                'totalDO' => $totalDO,
                'totalOperasional' => $totalOperasional,
                'pembayaran' => $pembayaran,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'tanggal' => $startDate
            ];
        } catch (\Exception $e) {
            Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }




    public function generatePdfReport($startDate, $endDate)
    {
        try {
            $perusahaan = Perusahaan::firstOrFail();

            $transaksiDo = TransaksiDo::with(['penjual', 'supir'])
                ->whereBetween('tanggal', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->orderBy('tanggal', 'asc')
                ->get();

            $operasional = Operasional::whereBetween('tanggal', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->get();

            // Pembayaran per metode DO
            $pembayaranTunai = $transaksiDo->where('cara_bayar', 'tunai')->sum('sisa_bayar');
            $pembayaranTransfer = $transaksiDo->where('cara_bayar', 'transfer')->sum('sisa_bayar');
            $pembayaranCairDiluar = $transaksiDo->where('cara_bayar', 'cair di luar')->sum('sisa_bayar');
            $pembayaranBelumDibayar = $transaksiDo->where('cara_bayar', 'belum dibayar')->sum('sisa_bayar');

            $pemasukanOperasional = $operasional->where('operasional', 'pemasukan')->sum('nominal');
            $pengeluaranOperasional = $operasional->where('operasional', 'pengeluaran')->sum('nominal');
            $totalBayarHutang = $transaksiDo->sum('pembayaran_hutang');

            // Hitung total pemasukan
            $sisaPembayaran = $transaksiDo->whereIn('cara_bayar', ['transfer', 'cair di luar', 'belum dibayar'])->sum('sisa_bayar');
            $totalPemasukan = $totalBayarHutang + // Total pembayaran hutang
                $sisaPembayaran + // Sisa pembayaran (transfer, cair di luar, belum dibayar)
                $pemasukanOperasional; // Pemasukan operasional

            // Hitung total pengeluaran
            $totalPengeluaran = $transaksiDo->sum('sub_total') + // Total DO
                $pengeluaranOperasional; // Pengeluaran operasional

            // Hitung total lainnya
            $totalTonase = $transaksiDo->sum('tonase');
            $totalSubTotal = $transaksiDo->sum('sub_total');
            $totalBiaya = $transaksiDo->sum(fn($item) => ($item->biaya_lain ?? 0) + ($item->upah_bongkar ?? 0));

            // Hitung sisa saldo (Selisih periode)
            $selisihPeriode = $totalPemasukan - $totalPengeluaran;

            // Fetch current company balance
            $perusahaanModel = Perusahaan::first();
            $currentSaldo = $perusahaanModel ? $perusahaanModel->saldo : 0;

            // Prepare breakdown for return
            $pembayaran = [
                'tunai' => $pembayaranTunai,
                'transfer' => $pembayaranTransfer,
                'cairDiluar' => $pembayaranCairDiluar,
                'belumDibayar' => $pembayaranBelumDibayar,
            ];

            $transaksiCount = [
                'total' => $transaksiDo->count(),
                'tunai' => $transaksiDo->where('cara_bayar', 'tunai')->count(),
                'transfer' => $transaksiDo->where('cara_bayar', 'transfer')->count(),
                'cairDiluar' => $transaksiDo->where('cara_bayar', 'cair di luar')->count(),
                'belumDibayar' => $transaksiDo->where('cara_bayar', 'belum dibayar')->count(),
            ];

            Log::info('Report Calculations:', [
                'pemasukan' => $totalPemasukan,
                'pengeluaran' => $totalPengeluaran,
                'selisih' => $selisihPeriode,
                'current_saldo' => $currentSaldo
            ]);

            return [
                'perusahaan' => $perusahaan,
                'transaksiDo' => $transaksiDo,
                'operasional' => $operasional,
                'totalPemasukan' => $totalPemasukan,
                'totalPengeluaran' => $totalPengeluaran,
                'selisihPeriode' => $selisihPeriode,
                'currentSaldo' => $currentSaldo,
                'pembayaran' => $pembayaran,
                'transaksiCount' => $transaksiCount,
                'totalTonase' => $totalTonase,
                'totalSubTotal' => $totalSubTotal,
                'totalBiaya' => $totalBiaya,
                'totalBayarHutang' => $totalBayarHutang,
                'pemasukanOperasional' => $pemasukanOperasional,
                'pengeluaranOperasional' => $pengeluaranOperasional,
                'tanggal' => $startDate
            ];
        } catch (\Exception $e) {
            Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
