<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenjualResource;
use App\Models\Penjual;
use Illuminate\Http\Request;

class PenjualController extends Controller
{
    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;

        $penjual = Penjual::where('perusahaan_id', $perusahaanId)
            ->withCount('transaksiDo')
            ->orderBy('nama')
            ->get();

        return PenjualResource::collection($penjual);
    }
}
