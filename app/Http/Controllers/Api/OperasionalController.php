<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operasional;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $operasional = Operasional::where('perusahaan_id', $perusahaan_id)->get();
        return response()->json($operasional);
    }
}
