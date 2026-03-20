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
        $perPage = $request->get('per_page', 20);
        $penjual = Penjual::where('perusahaan_id', $perusahaan_id)->latest()->paginate($perPage);
        return response()->json($penjual);
    }

    public function show($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $penjual = Penjual::where('perusahaan_id', $perusahaan_id)->findOrFail($id);
        return response()->json($penjual);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'hutang' => 'nullable|numeric',
        ]);

        $penjual = Penjual::create([
            'perusahaan_id' => $request->user()->perusahaan_id,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'hutang' => $request->hutang ?? 0,
        ]);

        return response()->json($penjual, 201);
    }
}
