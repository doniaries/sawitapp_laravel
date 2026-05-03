<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\HandlesOfflineSync;
use App\Http\Controllers\Controller;
use App\Models\TransaksiOperasional;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    use HandlesOfflineSync;

    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        $query = TransaksiOperasional::where('perusahaan_id', $perusahaanId);

        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        return $query->latest()->paginate($request->per_page ?? 10);
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
            ...$this->offlineSyncValidationRules(),
            'tanggal' => 'required|date',
            'kategori' => 'required',
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'pihak_id' => 'nullable|integer',
            'pihak_type' => 'nullable|string',
        ]);

        $kategori = \App\Enums\KategoriOperasional::tryFrom($request->kategori);
        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak valid'], 422);
        }

        if ($existing = $this->findExistingOfflineRecord(TransaksiOperasional::class, $request)) {
            return $this->idempotentResponse($existing, ['pihak']);
        }

        try {
            $operasional = TransaksiOperasional::create([
                ...$this->offlineSyncAttributes($request),
                'perusahaan_id' => $request->user()->perusahaan_id,
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal,
                'operasional' => $kategori->getJenisOperasional(),
                'kategori' => $kategori,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
                'pihak_id' => $request->pihak_id,
                'pihak_type' => $request->pihak_type,
                'is_from_transaksi' => false,
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateOfflineSyncKey($exception) && $existing = $this->findExistingOfflineRecord(TransaksiOperasional::class, $request)) {
                return $this->idempotentResponse($existing, ['pihak']);
            }

            throw $exception;
        }

        return response()->json($operasional->load('pihak'), 201);
    }
}
