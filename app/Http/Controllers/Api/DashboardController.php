<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalKeuangan;
use App\Models\Kendaraan;
use App\Models\Pekerja;
use App\Models\Penjual;
use App\Models\Perusahaan;
use App\Models\Supir;
use App\Models\TambahSaldo;
use App\Models\TransaksiDo;
use App\Models\TransaksiOperasional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $perusahaanId = $user->perusahaan_id;

        // Fail-safe untuk Admin/Superadmin tanpa perusahaan_id default
        if (is_null($perusahaanId) && $user->isAdminOrSuperAdmin()) {
            $firstPerusahaan = Perusahaan::first();
            $perusahaanId = $firstPerusahaan?->id;
            
            // Opsional: Update user agar permanen
            if ($perusahaanId) {
                $user->update(['perusahaan_id' => $perusahaanId]);
            }
        }

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $today = Carbon::today();

        $perusahaan = Perusahaan::find($perusahaanId);

        // Eloquent Counts
        $totalPenjual = Penjual::where('perusahaan_id', $perusahaanId)->count();
        $totalSupir = Supir::where('perusahaan_id', $perusahaanId)->count();
        $totalPekerja = Pekerja::where('perusahaan_id', $perusahaanId)->count();
        $totalKendaraan = Kendaraan::where('perusahaan_id', $perusahaanId)->count();
        $totalJurnal = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->count();
        $totalOperasional = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->count();
        $totalUser = User::where('perusahaan_id', $perusahaanId)->count();
        
        $saldoQuery = TambahSaldo::where('perusahaan_id', $perusahaanId);
        $totalPengajuanDana = (float) $saldoQuery->sum('nominal');
        $totalPengajuanCount = $saldoQuery->count();

        // Stats Keuangan via Jurnal Keuangan (Single Source of Truth)
        $jurnalQuery = JurnalKeuangan::where('perusahaan_id', $perusahaanId);
        
        // Pemasukan Today & Month
        $pemasukanTodayTotal = (float) $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pemasukan')
            ->whereDate('tanggal', $today)
            ->sum('nominal');
        $pemasukanTodayCount = $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pemasukan')
            ->whereDate('tanggal', $today)
            ->count();
            
        $pemasukanMonthTotal = (float) $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pemasukan')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->sum('nominal');
        $pemasukanMonthCount = $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pemasukan')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->count();
            
        // Pengeluaran Today & Month
        $pengeluaranTodayTotal = (float) $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pengeluaran')
            ->whereDate('tanggal', $today)
            ->sum('nominal');
        $pengeluaranTodayCount = $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pengeluaran')
            ->whereDate('tanggal', $today)
            ->count();
            
        $pengeluaranMonthTotal = (float) $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pengeluaran')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->sum('nominal');
        $pengeluaranMonthCount = $jurnalQuery->clone()
            ->where('jenis_transaksi', 'Pengeluaran')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->count();

        // Stats DO Counts & Amounts
        $doQuery = TransaksiDo::where('perusahaan_id', $perusahaanId);
        $doTodayCount = $doQuery->clone()->whereDate('tanggal', $today)->count();
        $doTodayAmount = (float) $doQuery->clone()->whereDate('tanggal', $today)->sum('sub_total');
        
        $yesterday = Carbon::yesterday();
        $doYesterdayCount = $doQuery->clone()->whereDate('tanggal', $yesterday)->count();
        
        $doMonthCount = $doQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        $doMonthAmount = (float) $doQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sub_total');

        // Latest transactions
        $transactions = TransaksiDo::where('perusahaan_id', $perusahaanId)
            ->with(['penjual:id,nama', 'supir:id,nama'])
            ->latest()->limit(5)->get()->map(fn($tx) => [
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
            ]);

        $latestOperasional = TransaksiOperasional::where('perusahaan_id', $perusahaanId)
            ->latest()->limit(5)->get();

        return response()->json([
            'saldo' => (float) ($perusahaan?->saldo ?? 0),
            'total_penjual' => $totalPenjual,
            'total_supir' => $totalSupir,
            'total_pekerja' => $totalPekerja,
            'total_kendaraan' => $totalKendaraan,
            'total_jurnal_keuangan' => $totalJurnal,
            'total_operasional' => $totalOperasional,
            'total_pengajuan_dana' => $totalPengajuanDana,
            'total_pengajuan_count' => $totalPengajuanCount,
            'total_user' => $totalUser,
            'transactions' => $transactions,
            'perusahaan_name' => $perusahaan?->name ?? '-',
            'latest_operasional' => $latestOperasional,
            'stats' => [
                'pemasukan' => [
                    'today' => [
                        'total' => $pemasukanTodayTotal,
                        'count' => $pemasukanTodayCount,
                    ],
                    'month' => [
                        'total' => $pemasukanMonthTotal,
                        'count' => $pemasukanMonthCount,
                    ],
                ],
                'pengeluaran' => [
                    'today' => [
                        'total' => $pengeluaranTodayTotal,
                        'count' => $pengeluaranTodayCount,
                    ],
                    'month' => [
                        'total' => $pengeluaranMonthTotal,
                        'count' => $pengeluaranMonthCount,
                    ],
                ],
                'transaksi' => [
                    'today' => [
                        'count' => $doTodayCount,
                        'total' => $doTodayAmount,
                    ],
                    'yesterday' => [
                        'count' => $doYesterdayCount,
                    ],
                    'month' => [
                        'count' => $doMonthCount,
                        'total' => $doMonthAmount,
                    ],
                    'periode_awal' => $today->startOfMonth()->format('Y-m-d'),
                    'periode_akhir' => $today->copy()->endOfMonth()->format('Y-m-d'),
                ]
            ]
        ]);
    }
}
