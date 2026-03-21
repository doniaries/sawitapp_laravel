<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supir;
use App\Models\Kendaraan;
use App\Actions\Finance\CreateResourceAction;
use Illuminate\Http\Request;

class LogistikController extends Controller
{
    public function supir(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $supir = Supir::latest()->paginate($perPage);
        return response()->json($supir);
    }

    public function showSupir($id, Request $request)
    {
        $supir = Supir::with(['transaksiDo', 'mutasiHutang' => function($query) {
                $query->latest()->limit(50);
            }])
            ->findOrFail($id);
        return response()->json($supir);
    }

    public function storeSupir(Request $request, CreateResourceAction $action)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'hutang' => 'nullable|numeric',
        ]);

        $supir = $action->execute('supir', $request->all());

        return response()->json($supir, 201);
    }

    public function updateSupir($id, Request $request)
    {
        $supir = Supir::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'status_supir' => 'nullable|string',
        ]);

        $supir->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'status_supir' => $request->status_supir,
        ]);

        return response()->json($supir);
    }

    public function kendaraan(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $kendaraan = Kendaraan::latest()->paginate($perPage);
        return response()->json($kendaraan);
    }

    public function showKendaraan($id, Request $request)
    {
        $kendaraan = Kendaraan::findOrFail($id);
        return response()->json($kendaraan);
    }

    public function storeKendaraan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_polisi' => 'required|string|max:20',
        ]);

        $kendaraan = Kendaraan::create([
            'nama' => $request->nama,
            'no_polisi' => $request->no_polisi,
            'is_maintenance' => false,
        ]);

        return response()->json($kendaraan, 201);
    }
}
