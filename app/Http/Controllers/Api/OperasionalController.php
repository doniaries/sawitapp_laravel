<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OperasionalResource;
use App\Models\Operasional;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        
        $operasional = Operasional::where('perusahaan_id', $perusahaanId)
            ->latest('tanggal')
            ->paginate($request->get('limit', 15));

        return OperasionalResource::collection($operasional);
    }
}
