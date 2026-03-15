<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjual;
use Illuminate\Http\Request;

class PenjualController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $penjual = Penjual::where('perusahaan_id', $perusahaan_id)->get();
        return response()->json($penjual);
    }
}
