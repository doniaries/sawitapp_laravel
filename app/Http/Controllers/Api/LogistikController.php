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
        $perPage = $request->get('per_page', 20);
        $supir = Supir::where('perusahaan_id', $perusahaan_id)->latest()->paginate($perPage);
        return response()->json($supir);
    }

    public function showSupir($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $supir = Supir::where('perusahaan_id', $perusahaan_id)->findOrFail($id);
        return response()->json($supir);
    }

    public function storeSupir(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'hutang' => 'nullable|numeric',
        ]);

        $supir = Supir::create([
            'perusahaan_id' => $request->user()->perusahaan_id,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'hutang' => $request->hutang ?? 0,
        ]);

        return response()->json($supir, 201);
    }

    public function kendaraan(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $perPage = $request->get('per_page', 20);
        $kendaraan = Kendaraan::where('perusahaan_id', $perusahaan_id)->latest()->paginate($perPage);
        return response()->json($kendaraan);
    }

    public function showKendaraan($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        $kendaraan = Kendaraan::where('perusahaan_id', $perusahaan_id)->findOrFail($id);
        return response()->json($kendaraan);
    }

    public function storeKendaraan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_polisi' => 'required|string|max:20',
        ]);

        $kendaraan = Kendaraan::create([
            'perusahaan_id' => $request->user()->perusahaan_id,
            'nama' => $request->nama,
            'no_polisi' => $request->no_polisi,
            'is_maintenance' => false,
        ]);

        return response()->json($kendaraan, 201);
    }
}
