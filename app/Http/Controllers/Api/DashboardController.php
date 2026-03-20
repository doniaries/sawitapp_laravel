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
        $operasionalMasuk = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $pemasukanTotal = $pembayaranHutang + $operasionalMasuk;

        // Pengeluaran
        $doPengeluaran = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sisa_bayar');
        $operasionalKeluar = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $pengeluaranTotal = $doPengeluaran + $operasionalKeluar;

        // Transaksi
        $totalTransaksi = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

        return response()->json([
            'saldo' => $perusahaan->saldo ?? 0,
            'total_penjual' => $totalSellers,
            'total_supir' => $totalDrivers,
            'total_jurnal_keuangan' => $totalJurnal,
            'total_pengajuan_dana' => $totalDana,
            'perusahaan_name' => $perusahaan->name ?? '-',
            'stats' => [
                'pemasukan' => [
                    'total' => $pemasukanTotal,
                    'hutang' => $pembayaranHutang,
                    'sisa' => 0, // Placeholder jika ada logika sisa nanti
                    'operasional' => $operasionalMasuk,
                ],
                'pengeluaran' => [
                    'total' => $pengeluaranTotal,
                    'do' => $doPengeluaran,
                    'operasional' => $operasionalKeluar,
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
