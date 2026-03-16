<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDana;
use Illuminate\Http\Request;

class PengajuanDanaController extends Controller
{
    public function index(Request $request)
    {
        $perusahaan_id = $request->user()->perusahaan_id;
        
        $query = PengajuanDana::where('perusahaan_id', $perusahaan_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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
            'nominal' => 'required|numeric',
            'keperluan' => 'required|string',
            'tanggal_pengajuan' => 'required|date',
        ]);

        $pengajuan = PengajuanDana::create([
            'perusahaan_id' => $request->user()->perusahaan_id,
            'user_id' => $request->user()->id,
            'nominal' => $request->nominal,
            'keperluan' => $request->keperluan,
            'tanggal_pengajuan' => $request->tanggal_pengajuan,
            'status' => 'pending',
        ]);

        return response()->json($pengajuan, 201);
    }
}
