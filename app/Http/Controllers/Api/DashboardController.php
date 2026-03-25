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
        
        $pendingSaldoQuery = TambahSaldo::where('perusahaan_id', $perusahaanId)->where('status', 'pending');
        $totalPengajuanDana = (float) $pendingSaldoQuery->sum('nominal');
        $totalPengajuanCount = $pendingSaldoQuery->count();

        // Stats Pemasukan (Operasional)
        $opPemasukanQuery = TransaksiOperasional::where('perusahaan_id', $perusahaanId)
            ->where('operasional', 'pemasukan');
            
        $pemasukanTodayTotal = (float) $opPemasukanQuery->clone()->whereDate('tanggal', $today)->sum('nominal');
        $pemasukanTodayCount = $opPemasukanQuery->clone()->whereDate('tanggal', $today)->count();
        $pemasukanMonthTotal = (float) $opPemasukanQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $pemasukanMonthCount = $opPemasukanQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

        // Stats Pengeluaran (Operasional)
        $opPengeluaranQuery = TransaksiOperasional::where('perusahaan_id', $perusahaanId)
            ->where('operasional', 'pengeluaran');
            
        $opPengeluaranTodayTotal = (float) $opPengeluaranQuery->clone()->whereDate('tanggal', $today)->sum('nominal');
        $opPengeluaranTodayCount = $opPengeluaranQuery->clone()->whereDate('tanggal', $today)->count();
        $opPengeluaranMonthTotal = (float) $opPengeluaranQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');
        $opPengeluaranMonthCount = $opPengeluaranQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

        // Stats DO
        $doQuery = TransaksiDo::where('perusahaan_id', $perusahaanId);
        
        $doTodayCount = $doQuery->clone()->whereDate('tanggal', $today)->count();
        $doMonthCount = $doQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();
        
        $doHutangTodayTotal = (float) $doQuery->clone()->whereDate('tanggal', $today)->sum('pembayaran_hutang');
        $doHutangTodayCount = $doQuery->clone()->whereDate('tanggal', $today)->where('pembayaran_hutang', '>', 0)->count();
        
        $doPengeluaranMonthTotal = (float) $doQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sisa_bayar');
        $doPengeluaranMonthCount = $doQuery->clone()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->where('sisa_bayar', '>', 0)->count();

        // Merged stats for UI
        $pengeluaranTodayTotal = $doHutangTodayTotal + $opPengeluaranTodayTotal;
        $pengeluaranTodayCount = $doHutangTodayCount + $opPengeluaranTodayCount;
        $pengeluaranMonthTotal = $doPengeluaranMonthTotal + $opPengeluaranMonthTotal;
        $pengeluaranMonthCount = $doPengeluaranMonthCount + $opPengeluaranMonthCount;

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
                        'total' => $doTodayCount,
                    ],
                    'month' => [
                        'total' => $doMonthCount,
                    ],
                    'periode_awal' => $today->startOfMonth()->format('Y-m-d'),
                    'periode_akhir' => $today->copy()->endOfMonth()->format('Y-m-d'),
                ]
            ]
        ]);
    }
}
