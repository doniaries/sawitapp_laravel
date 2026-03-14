<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupirResource;
use App\Http\Resources\KendaraanResource;
use App\Models\Supir;
use App\Models\Kendaraan;
use Illuminate\Http\Request;

class LogistikController extends Controller
{
    public function supir(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        $supir = Supir::where('perusahaan_id', $perusahaanId)
            ->withCount('kendaraan')
            ->orderBy('nama')
            ->get();

        return SupirResource::collection($supir);
    }

    public function kendaraan(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        $kendaraan = Kendaraan::where('perusahaan_id', $perusahaanId)
            ->with('supir')
            ->orderBy('no_polisi')
            ->get();

        return KendaraanResource::collection($kendaraan);
    }
}
