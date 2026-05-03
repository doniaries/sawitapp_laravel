<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\HandlesOfflineSync;
use App\Http\Controllers\Controller;
use App\Models\TambahSaldo as PengajuanDana;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Enums\TipeNama;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TambahSaldoController extends Controller
{
    use HandlesOfflineSync;

    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        $query = PengajuanDana::where('perusahaan_id', $perusahaanId);

        return response()->json(
            $query->with('user')->latest()->paginate(20)
        );
    }

    public function show($id, Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        
        $pengajuan = PengajuanDana::where('perusahaan_id', $perusahaan_id)
            ->with('user')
            ->findOrFail($id);

        return response()->json($pengajuan);
    }

    public function store(Request $request)
    {
        $request->validate([
            ...$this->offlineSyncValidationRules(),
            'nominal' => 'required|numeric',
            'keterangan' => 'required|string',
            'tanggal' => 'required|date',
            'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($existing = $this->findExistingOfflineRecord(PengajuanDana::class, $request)) {
            return $this->idempotentResponse($existing);
        }

        return DB::transaction(function () use ($request, $user) {
            $buktiPath = null;
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $filename = 'transfer_' . time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/bukti_transfer', $filename);
                $buktiPath = 'bukti_transfer/' . $filename;
            }

            try {
                $pengajuan = PengajuanDana::create([
                    ...$this->offlineSyncAttributes($request),
                    'perusahaan_id' => $user->perusahaan_id,
                    'user_id' => $user->id,
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                    'tanggal' => $request->tanggal,
                    'bukti_transfer' => $buktiPath,
                ]);
            } catch (QueryException $exception) {
                if ($this->isDuplicateOfflineSyncKey($exception) && $existing = $this->findExistingOfflineRecord(PengajuanDana::class, $request)) {
                    return $this->idempotentResponse($existing);
                }

                throw $exception;
            }

            return response()->json($pengajuan, 201);
        });
    }
}
