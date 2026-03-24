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
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $today = Carbon::today();

        $perusahaan = Perusahaan::find($perusahaanId);

        // Gabungkan semua count dalam 1 query
        $counts = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM penjual WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_penjual,
                (SELECT COUNT(*) FROM supir WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_supir,
                (SELECT COUNT(*) FROM pekerja WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_pekerja,
                (SELECT COUNT(*) FROM kendaraan WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_kendaraan,
                (SELECT COUNT(*) FROM jurnal_keuangan WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_jurnal,
                (SELECT COUNT(*) FROM transaksi_operasional WHERE perusahaan_id = ? AND deleted_at IS NULL) as total_operasional,
                (SELECT COUNT(*) FROM users WHERE perusahaan_id = ?) as total_user,
                (SELECT COALESCE(SUM(nominal), 0) FROM tambah_saldo WHERE perusahaan_id = ? AND status = 'pending' AND deleted_at IS NULL) as total_pengajuan_dana,
                (SELECT COUNT(*) FROM tambah_saldo WHERE perusahaan_id = ? AND status = 'pending' AND deleted_at IS NULL) as total_pengajuan_count
        ", [$perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId, $perusahaanId]);

        // Gabungkan semua stats operasional dalam 1 query
        $opStats = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN operasional = 'pemasukan' AND DATE(tanggal) = ? THEN nominal ELSE 0 END), 0) as pemasukan_today_total,
                SUM(CASE WHEN operasional = 'pemasukan' AND DATE(tanggal) = ? THEN 1 ELSE 0 END) as pemasukan_today_count,
                COALESCE(SUM(CASE WHEN operasional = 'pemasukan' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN nominal ELSE 0 END), 0) as pemasukan_month_total,
                SUM(CASE WHEN operasional = 'pemasukan' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN 1 ELSE 0 END) as pemasukan_month_count,
                COALESCE(SUM(CASE WHEN operasional = 'pengeluaran' AND DATE(tanggal) = ? THEN nominal ELSE 0 END), 0) as pengeluaran_today_total,
                SUM(CASE WHEN operasional = 'pengeluaran' AND DATE(tanggal) = ? THEN 1 ELSE 0 END) as pengeluaran_today_count,
                COALESCE(SUM(CASE WHEN operasional = 'pengeluaran' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN nominal ELSE 0 END), 0) as pengeluaran_month_total,
                SUM(CASE WHEN operasional = 'pengeluaran' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN 1 ELSE 0 END) as pengeluaran_month_count
            FROM transaksi_operasional
            WHERE perusahaan_id = ? AND deleted_at IS NULL
        ", [$today, $today, $month, $year, $month, $year, $today, $today, $month, $year, $month, $year, $perusahaanId]);

        // Stats DO
        $doStats = DB::selectOne("
            SELECT
                SUM(CASE WHEN DATE(tanggal) = ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN 1 ELSE 0 END) as month_count,
                COALESCE(SUM(CASE WHEN DATE(tanggal) = ? THEN pembayaran_hutang ELSE 0 END), 0) as hutang_today,
                SUM(CASE WHEN DATE(tanggal) = ? AND pembayaran_hutang > 0 THEN 1 ELSE 0 END) as hutang_today_count,
                COALESCE(SUM(CASE WHEN MONTH(tanggal) = ? AND YEAR(tanggal) = ? THEN sisa_bayar ELSE 0 END), 0) as pengeluaran_month,
                SUM(CASE WHEN MONTH(tanggal) = ? AND YEAR(tanggal) = ? AND sisa_bayar > 0 THEN 1 ELSE 0 END) as pengeluaran_month_count
            FROM transaksi_do
            WHERE perusahaan_id = ? AND deleted_at IS NULL
        ", [$today, $month, $year, $today, $today, $month, $year, $month, $year, $perusahaanId]);

        // Latest transactions dan operasional (2 query ringan)
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

        $pengeluaranTodayTotal = ($doStats->hutang_today ?? 0) + ($opStats->pengeluaran_today_total ?? 0);
        $pengeluaranTodayCount = ($doStats->hutang_today_count ?? 0) + ($opStats->pengeluaran_today_count ?? 0);
        $pengeluaranMonthTotal = ($doStats->pengeluaran_month ?? 0) + ($opStats->pengeluaran_month_total ?? 0);
        $pengeluaranMonthCount = ($doStats->pengeluaran_month_count ?? 0) + ($opStats->pengeluaran_month_count ?? 0);

        return response()->json([
            'saldo' => $perusahaan?->saldo ?? 0,
            'total_penjual' => (int) $counts->total_penjual,
            'total_supir' => (int) $counts->total_supir,
            'total_pekerja' => (int) $counts->total_pekerja,
            'total_kendaraan' => (int) $counts->total_kendaraan,
            'total_jurnal_keuangan' => (int) $counts->total_jurnal,
            'total_operasional' => (int) $counts->total_operasional,
            'total_pengajuan_dana' => (float) $counts->total_pengajuan_dana,
            'total_pengajuan_count' => (int) $counts->total_pengajuan_count,
            'total_user' => (int) $counts->total_user,
            'transactions' => $transactions,
            'perusahaan_name' => $perusahaan?->name ?? '-',
            'latest_operasional' => $latestOperasional,
            'stats' => [
                'pemasukan' => [
                    'today' => [
                        'total' => (float) ($opStats->pemasukan_today_total ?? 0),
                        'count' => (int) ($opStats->pemasukan_today_count ?? 0),
                    ],
                    'month' => [
                        'total' => (float) ($opStats->pemasukan_month_total ?? 0),
                        'count' => (int) ($opStats->pemasukan_month_count ?? 0),
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
                        'total' => (int) ($doStats->today_count ?? 0),
                    ],
                    'month' => [
                        'total' => (int) ($doStats->month_count ?? 0),
                    ],
                    'periode_awal' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'periode_akhir' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                ]
            ]
        ]);
    }
}
