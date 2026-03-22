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
        $today = Carbon::today();

        // Pemasukan Hari Ini (Hanya dari Operasional yang jenisnya pemasukan)
        $operasionalMasukToday = TransaksiOperasional::where('operasional', 'pemasukan')->whereDate('tanggal', $today)->sum('nominal');
        $countOpMasukToday = TransaksiOperasional::where('operasional', 'pemasukan')->whereDate('tanggal', $today)->count();
        
        $pemasukanTodayTotal = $operasionalMasukToday;
        $pemasukanTodayCount = $countOpMasukToday;

        // Pemasukan Bulan Ini
        $operasionalMasukMonth = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $countOpMasukMonth = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $pemasukanMonthTotal = $operasionalMasukMonth;
        $pemasukanMonthCount = $countOpMasukMonth;

        // Pengeluaran Hari Ini (Operasional Keluar + DO cash payment)
        $pembayaranHutangToday = TransaksiDo::whereDate('tanggal', $today)->sum('pembayaran_hutang');
        $countHutangToday = TransaksiDo::whereDate('tanggal', $today)->where('pembayaran_hutang', '>', 0)->count();
        $operasionalKeluarToday = TransaksiOperasional::where('operasional', 'pengeluaran')->whereDate('tanggal', $today)->sum('nominal');
        $countOpKeluarToday = TransaksiOperasional::where('operasional', 'pengeluaran')->whereDate('tanggal', $today)->count();
        
        $pengeluaranTodayTotal = $pembayaranHutangToday + $operasionalKeluarToday;
        $pengeluaranTodayCount = $countHutangToday + $countOpKeluarToday;

        // Pengeluaran Bulan Ini
        $pembayaranHutangMonth = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('pembayaran_hutang');
        $countHutangMonth = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('pembayaran_hutang', '>', 0)->count();
        $operasionalKeluarMonth = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $countOpKeluarMonth = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $pengeluaranMonthTotal = $pembayaranHutangMonth + $operasionalKeluarMonth;
        $pengeluaranMonthCount = $countHutangMonth + $countOpKeluarMonth;

        // Pengeluaran Bulan Ini
        $doPengeluaranMonth = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sisa_bayar');
        $countDoPengeluaranMonth = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('sisa_bayar', '>', 0)->count();
        $operasionalKeluarMonth = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $countOpKeluarMonth = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $pengeluaranMonthTotal = $doPengeluaranMonth + $operasionalKeluarMonth;
        $pengeluaranMonthCount = $countDoPengeluaranMonth + $countOpKeluarMonth;

        // Transaksi (DO)
        $totalTransaksiToday = TransaksiDo::whereDate('tanggal', $today)->count();
        $totalTransaksiMonth = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

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
                    'today' => [
                        'total' => (float) $pemasukanTodayTotal,
                        'count' => (int) $pemasukanTodayCount,
                    ],
                    'month' => [
                        'total' => (float) $pemasukanMonthTotal,
                        'count' => (int) $pemasukanMonthCount,
                    ],
                ],
                'pengeluaran' => [
                    'today' => [
                        'total' => (float) $pengeluaranTodayTotal,
                        'count' => (int) $pengeluaranTodayCount,
                    ],
                    'month' => [
                        'total' => (float) $pengeluaranMonthTotal,
                        'count' => (int) $pengeluaranMonthCount,
                    ],
                ],
                'transaksi' => [
                    'today' => [
                        'total' => (int) $totalTransaksiToday,
                    ],
                    'month' => [
                        'total' => (int) $totalTransaksiMonth,
                    ],
                    'periode_awal' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'periode_akhir' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                ]
            ]
        ]);
    }
}
