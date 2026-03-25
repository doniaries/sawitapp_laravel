<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TambahSaldo as PengajuanDana;
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;
use App\Enums\TipeNama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TambahSaldoController extends Controller
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

        $user = $request->user();
        $role = strtolower($user->role ?? '');
        $isDirect = in_array($role, ['admin', 'pimpinan', 'super_admin']);

        return DB::transaction(function () use ($request, $user, $isDirect) {
            $pengajuan = PengajuanDana::create([
                'perusahaan_id' => $user->perusahaan_id,
                'user_id' => $user->id,
                'nominal' => $request->nominal,
                'keperluan' => $request->keperluan,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'status' => $isDirect ? PengajuanDana::STATUS_DISETUJUI : PengajuanDana::STATUS_PENDING,
                'tanggal_proses' => $isDirect ? now() : null,
                'proses_by' => $isDirect ? $user->id : null,
            ]);

            if ($isDirect) {
                // Tambah Saldo Perusahaan Langsung
                $perusahaan = Perusahaan::findOrFail($user->perusahaan_id);
                $saldoAwal = $perusahaan->saldo;
                $perusahaan->increment('saldo', $pengajuan->nominal);
                $saldoAkhir = $perusahaan->saldo;

                // Catat di Jurnal Keuangan
                JurnalKeuangan::create([
                    'perusahaan_id' => $pengajuan->perusahaan_id,
                    'tanggal' => now(),
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => JurnalKeuangan::KATEGORI_TRANSAKSI['SALDO'],
                    'sub_kategori' => JurnalKeuangan::SUB_KATEGORI_SALDO['TAMBAH'],
                    'nominal' => $pengajuan->nominal,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'sumber_transaksi' => 'Tambah Saldo',
                    'referensi_id' => $pengajuan->id,
                    'nomor_referensi' => $pengajuan->nomor ?? ('TS-' . $pengajuan->id),
                    'pihak_terkait' => $user->name,
                    'tipe_pihak' => TipeNama::USER,
                    'cara_pembayaran' => 'transfer',
                    'keterangan' => 'Top up saldo langsung: ' . $pengajuan->keperluan,
                    'mempengaruhi_kas' => true,
                ]);
            }

            return response()->json($pengajuan, 201);
        });
    }

    public function approve($id, Request $request)
    {
        $pengajuan = PengajuanDana::findOrFail($id);

        if ($pengajuan->status !== PengajuanDana::STATUS_PENDING) {
            return response()->json(['message' => 'Pengajuan sudah diproses.'], 422);
        }

        $request->validate([
            'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'catatan_pimpinan' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($pengajuan, $request) {
                $buktiPath = null;
                if ($request->hasFile('bukti_transfer')) {
                    $file = $request->file('bukti_transfer');
                    $filename = 'transfer_' . time() . '_' . $pengajuan->id . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/bukti_transfer', $filename);
                    $buktiPath = 'bukti_transfer/' . $filename;
                }

                // 1. Update Pengajuan
                $pengajuan->update([
                    'status' => PengajuanDana::STATUS_DISETUJUI,
                    'tanggal_proses' => now(),
                    'proses_by' => $request->user()->id,
                    'bukti_transfer' => $buktiPath,
                    'catatan_pimpinan' => $request->catatan_pimpinan,
                ]);

                // 2. Tambah Saldo Perusahaan
                $perusahaan = Perusahaan::findOrFail($pengajuan->perusahaan_id);
                $saldoAwal = $perusahaan->saldo;
                $perusahaan->increment('saldo', $pengajuan->nominal);
                $saldoAkhir = $perusahaan->saldo;

                // 3. Catat di Jurnal Keuangan (Buku Kas)
                JurnalKeuangan::create([
                    'perusahaan_id' => $pengajuan->perusahaan_id,
                    'tanggal' => now(),
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => JurnalKeuangan::KATEGORI_TRANSAKSI['SALDO'],
                    'sub_kategori' => JurnalKeuangan::SUB_KATEGORI_SALDO['TAMBAH'],
                    'nominal' => $pengajuan->nominal,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'sumber_transaksi' => 'Tambah Saldo',
                    'referensi_id' => $pengajuan->id,
                    'nomor_referensi' => $pengajuan->nomor ?? ('TS-' . $pengajuan->id),
                    'pihak_terkait' => $pengajuan->user?->name ?? 'User',
                    'tipe_pihak' => TipeNama::USER,
                    'cara_pembayaran' => 'transfer',
                    'keterangan' => 'Top up saldo: ' . $pengajuan->keperluan,
                    'mempengaruhi_kas' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan tambah saldo disetujui, saldo diperbarui, dan jurnal kas telah dicatat.',
                    'data' => $pengajuan->fresh('user'),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id, Request $request)
    {
        $request->validate([
            'catatan_pimpinan' => 'required|string',
        ]);

        $pengajuan = PengajuanDana::findOrFail($id);

        if ($pengajuan->status !== PengajuanDana::STATUS_PENDING) {
            return response()->json(['message' => 'Pengajuan sudah diproses.'], 422);
        }

        $pengajuan->update([
            'status' => PengajuanDana::STATUS_DITOLAK,
            'tanggal_proses' => now(),
            'proses_by' => $request->user()->id,
            'catatan_pimpinan' => $request->catatan_pimpinan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ditolak.',
            'data' => $pengajuan,
        ]);
    }
}
