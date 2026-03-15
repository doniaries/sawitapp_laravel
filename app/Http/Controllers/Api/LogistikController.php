<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supir;
use App\Models\Kendaraan;
use Illuminate\Http\Request;

class LogistikController extends Controller
{
    public function supir(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $supir = Supir::where('perusahaan_id', $perusahaan_id)->isNotMaintenance()->get();
        return response()->json($supir);
    }

    public function kendaraan(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $kendaraan = Kendaraan::where('perusahaan_id', $perusahaan_id)->isNotMaintenance()->get();
        return response()->json($kendaraan);
    }
}
