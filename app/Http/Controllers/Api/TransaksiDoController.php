<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransaksiDoResource;
use App\Models\TransaksiDo;
use Illuminate\Http\Request;

class TransaksiDoController extends Controller
{
    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        
        $transaksi = TransaksiDo::with(['penjual', 'supir', 'kendaraan'])
            ->where('perusahaan_id', $perusahaanId)
            ->latest('tanggal')
            ->paginate($request->get('limit', 15));

        return TransaksiDoResource::collection($transaksi);
    }

    public function show($id)
    {
        $transaksi = TransaksiDo::with(['penjual', 'supir', 'kendaraan'])->findOrFail($id);
        
        return new TransaksiDoResource($transaksi);
    }
}
