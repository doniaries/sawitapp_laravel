<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjual;
use App\Actions\Finance\CreateResourceAction;
use Illuminate\Http\Request;

class PenjualController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $penjual = Penjual::latest()->paginate($perPage);
        return response()->json($penjual);
    }

    public function show($id, Request $request)
    {
        $penjual = Penjual::with(['transaksiDo', 'mutasiHutang' => function($query) {
                $query->latest()->limit(50);
            }])
            ->findOrFail($id);
        return response()->json($penjual);
    }

    public function store(Request $request, CreateResourceAction $action)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'hutang' => 'nullable|numeric',
        ]);

        $penjual = $action->execute('penjual', $request->all());

        return response()->json($penjual, 201);
    }

    public function update($id, Request $request)
    {
        $penjual = Penjual::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
        ]);

        $penjual->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return response()->json($penjual);
    }
}
