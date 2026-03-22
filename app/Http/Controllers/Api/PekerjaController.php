<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pekerja;
use App\Actions\Finance\CreateResourceAction;
use Illuminate\Http\Request;

class PekerjaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $pekerja = Pekerja::latest()->paginate($perPage);
        return response()->json($pekerja);
    }

    public function show($id, Request $request)
    {
        $pekerja = Pekerja::with(['operasional', 'jurnalKeuangan' => function($query) {
                $query->latest()->limit(50);
            }])
            ->findOrFail($id);
        return response()->json($pekerja);
    }

    public function store(Request $request, CreateResourceAction $action)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'hutang' => 'nullable|numeric',
        ]);

        $pekerja = $action->execute('pekerja', $request->all());

        return response()->json($pekerja, 201);
    }

    public function update($id, Request $request)
    {
        $pekerja = Pekerja::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
        ]);

        $pekerja->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return response()->json($pekerja);
    }
}
