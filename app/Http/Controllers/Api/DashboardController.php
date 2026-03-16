<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\PengajuanDana;
use App\Models\Perusahaan;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $perusahaanId = $user->perusahaan_id;

        $perusahaan = Perusahaan::find($perusahaanId);
        
        $totalSellers = Penjual::where('perusahaan_id', $perusahaanId)->count();
        $totalDrivers = Supir::where('perusahaan_id', $perusahaanId)->count();
        $totalDana = PengajuanDana::where('perusahaan_id', $perusahaanId)
            ->where('status', 'pending')
            ->sum('nominal');

        return response()->json([
            'saldo' => $perusahaan->saldo ?? 0,
            'total_penjual' => $totalSellers,
            'total_supir' => $totalDrivers,
            'total_pengajuan_dana' => $totalDana,
            'perusahaan_name' => $perusahaan->name ?? '-',
        ]);
    }
}
