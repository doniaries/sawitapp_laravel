<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiOperasional;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $perPage = $request->get('per_page', 20);
        $operasional = TransaksiOperasional::where('perusahaan_id', $perusahaan_id)->latest()->paginate($perPage);
        return response()->json($operasional);
    }

    public function show($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $operasional = TransaksiOperasional::where('perusahaan_id', $perusahaan_id)
            ->with(['pihak', 'user'])
            ->findOrFail($id);
        return response()->json($operasional);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required',
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'pihak_id' => 'nullable|integer',
            'pihak_type' => 'nullable|string',
        ]);

        $operasional = TransaksiOperasional::create([
            'perusahaan_id' => $request->user()->perusahaan_id,
            'user_id' => $request->user()->id,
            'tanggal' => $request->tanggal,
            'kategori' => $request->kategori,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'pihak_id' => $request->pihak_id,
            'pihak_type' => $request->pihak_type,
            'is_from_transaksi' => false,
        ]);

        return response()->json($operasional, 201);
    }
}
