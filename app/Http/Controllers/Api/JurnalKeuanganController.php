<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JurnalKeuangan;

class JurnalKeuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perusahaanId = $user->perusahaan_id;

        $query = JurnalKeuangan::where('perusahaan_id', $perusahaanId);

        if ($request->has('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }

        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderBy('tanggal', 'desc')->paginate($perPage);

        // Agregat statistik (untuk header di mobile)
        $statsQuery = JurnalKeuangan::where('perusahaan_id', $perusahaanId);
        
        // Sesuaikan filter jika ada
        if ($request->has('start_date') && $request->has('end_date')) {
            $statsQuery->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $summary = [
            'total_pemasukan' => (float) $statsQuery->clone()->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
            'total_pengeluaran' => (float) $statsQuery->clone()->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
        ];

        return response()->json(array_merge($paginated->toArray(), [
            'summary' => $summary
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
