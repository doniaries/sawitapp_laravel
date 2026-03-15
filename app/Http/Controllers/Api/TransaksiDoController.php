<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDo;
use Illuminate\Http\Request;

class TransaksiDoController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        
        $query = TransaksiDo::where('perusahaan_id', $perusahaan_id);

        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        return response()->json(
            $query->with(['supir', 'kendaraan', 'penjual'])->latest()->paginate(20)
        );
    }

    public function show($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        
        $transaksi = TransaksiDo::where('perusahaan_id', $perusahaan_id)
            ->with(['supir', 'kendaraan', 'penjual', 'detailTransaksiDo', 'pembayaranHutang'])
            ->findOrFail($id);

        return response()->json($transaksi);
    }
}
