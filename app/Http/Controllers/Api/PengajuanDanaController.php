<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDana;
use Illuminate\Http\Request;

class PengajuanDanaController extends Controller
{
    public function index(Request $request)
    {
        $perusahaanId = $request->user()->perusahaan_id;
        $query = PengajuanDana::where('perusahaan_id', $perusahaanId);

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

    public function approve($id, Request $request)
    {
        $request->validate([
            'bukti_transfer' => 'nullable|string',
            'catatan_pimpinan' => 'nullable|string',
        ]);

        $pengajuan = PengajuanDana::where('perusahaan_id', $request->user()->perusahaan_id)
            ->findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan sudah diproses.'], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($pengajuan, $request) {
            $pengajuan->update([
                'status' => 'disetujui',
                'tanggal_proses' => now(),
                'proses_by' => $request->user()->id,
                'bukti_transfer' => $request->bukti_transfer,
                'catatan_pimpinan' => $request->catatan_pimpinan,
            ]);

            \App\Models\JurnalKeuangan::create([
                'perusahaan_id' => $pengajuan->perusahaan_id,
                'tanggal' => now(),
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'Saldo',
                'sub_kategori' => 'Tambah Saldo',
                'nominal' => $pengajuan->nominal,
                'referensi_id' => $pengajuan->id,
                'sumber_transaksi' => 'Pengajuan Dana',
                'tipe_pihak' => 'user',
                'cara_pembayaran' => 'transfer',
                'pihak_terkait' => $pengajuan->user?->name,
                'keterangan' => 'Penambahan saldo dari pengajuan dana #' . $pengajuan->id . ': ' . $pengajuan->keperluan,
                'mempengaruhi_kas' => true,
            ]);
        });

        return response()->json($pengajuan->fresh());
    }

    public function reject($id, Request $request)
    {
        $request->validate([
            'catatan_pimpinan' => 'required|string',
        ]);

        $pengajuan = PengajuanDana::where('perusahaan_id', $request->user()->perusahaan_id)
            ->findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan sudah diproses.'], 422);
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'tanggal_proses' => now(),
            'proses_by' => $request->user()->id,
            'catatan_pimpinan' => $request->catatan_pimpinan,
        ]);

        return response()->json($pengajuan);
    }
}
