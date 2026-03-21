<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\PengajuanDana;
use App\Models\Perusahaan;

use App\Models\JurnalKeuangan;
use App\Models\TransaksiDo;
use App\Models\TransaksiOperasional;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $perusahaanId = $user->perusahaan_id;

        $perusahaan = Perusahaan::find($perusahaanId);
        
        $totalSellers = Penjual::where('perusahaan_id', $perusahaanId)->count();
        $totalDrivers = Supir::where('perusahaan_id', $perusahaanId)->count();
        $totalJurnal = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->count();
        $totalDana = PengajuanDana::where('perusahaan_id', $perusahaanId)
            ->where('status', 'pending')
            ->sum('nominal');

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        // Pemasukan
        $pembayaranHutang = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('pembayaran_hutang');
        $countHutang = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('pembayaran_hutang', '>', 0)->count();
        
        $operasionalMasuk = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $countOpMasuk = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $pemasukanTotal = $pembayaranHutang + $operasionalMasuk;
        $totalPemasukanCount = $countHutang + $countOpMasuk;

        // Pengeluaran
        $doPengeluaran = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sisa_bayar');
        $countDoPengeluaran = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('sisa_bayar', '>', 0)->count();
        
        $operasionalKeluar = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $countOpKeluar = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $pengeluaranTotal = $doPengeluaran + $operasionalKeluar;
        $totalPengeluaranCount = $countDoPengeluaran + $countOpKeluar;

        // Transaksi
        $totalTransaksi = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

        return response()->json([
            'saldo' => $perusahaan?->saldo ?? 0,
            'total_penjual' => $totalSellers,
            'total_supir' => $totalDrivers,
            'total_jurnal_keuangan' => $totalJurnal,
            'total_pengajuan_dana' => $totalDana,
            'transactions' => TransaksiDo::with(['penjual:id,nama', 'supir:id,nama'])->latest()->limit(5)->get()->map(function($tx) {
                return [
                    'id' => $tx->id,
                    'nomor' => $tx->nomor,
                    'tanggal' => $tx->tanggal,
                    'tonase' => $tx->tonase,
                    'harga_satuan' => $tx->harga_satuan,
                    'sub_total' => $tx->sub_total,
                    'upah_bongkar' => $tx->upah_bongkar,
                    'biaya_lain' => $tx->biaya_lain,
                    'keterangan_biaya_lain' => $tx->keterangan_biaya_lain,
                    'hutang_awal' => $tx->hutang_awal,
                    'pembayaran_hutang' => $tx->pembayaran_hutang,
                    'sisa_bayar' => $tx->sisa_bayar,
                    'cara_bayar' => $tx->cara_bayar,
                    'sisa_hutang_penjual' => $tx->sisa_hutang_penjual,
                    'penjual_nama' => $tx->penjual?->nama,
                    'supir_nama' => $tx->supir?->nama,
                    'no_polisi' => $tx->no_polisi,
                ];
            }),
            'perusahaan_name' => $perusahaan?->name ?? '-',
            'stats' => [
                'pemasukan' => [
                    'total' => $pemasukanTotal,
                    'hutang' => $pembayaranHutang,
                    'sisa' => 0,
                    'operasional' => $operasionalMasuk,
                    'count' => (int) $totalPemasukanCount,
                ],
                'pengeluaran' => [
                    'total' => $pengeluaranTotal,
                    'do' => $doPengeluaran,
                    'operasional' => $operasionalKeluar,
                    'count' => (int) $totalPengeluaranCount,
                ],
                'transaksi' => [
                    'total' => $totalTransaksi,
                    'periode_awal' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'periode_akhir' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                ]
            ]
        ]);
    }
}
