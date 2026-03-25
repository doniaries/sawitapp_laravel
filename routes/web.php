<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Settings\ManageSettings;

// Route::get('/', function () {
//     return view('welcome');
// });

//langsung ke halaman login
// Route::get('/', function () {
//     return redirect('/admin');
// });

//production
Route::redirect('/', '/admin');

Route::get('login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::middleware(['auth', 'check.perusahaan'])->group(function () {
    // routes yang membutuhkan data perusahaan
});

// Route::get('transaksi-do/{id}/pdf', function ($id) {
//     $transaksi = \App\Models\TransaksiDo::findOrFail($id);
//     return $transaksi->generatePdf();
// })->name('transaksi-do.pdf');

// Pastikan route PDF dalam middleware auth
Route::middleware(['auth'])->group(function () {
    Route::get('transaksi-do/{id}/pdf', function ($id) {
        $transaksi = \App\Models\TransaksiDo::findOrFail($id);
        return $transaksi->generatePdf();
    })->name('transaksi-do.pdf');

    Route::get('jurnal-keuangan/rekap', function (\Illuminate\Http\Request $request, \App\Services\JurnalKeuanganService $service) {
        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $tab = $request->query('tab', 'hari_ini');

        // Logic based on tab if dates are not specific
        if ($request->has('tab') && !$request->has('start_date')) {
            if ($tab === 'bulan_ini') {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            } elseif ($tab === 'tahun_ini') {
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
            }
        }

        $viewData = $service->generatePdfReport($startDate, $endDate);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.keuangan-harian', $viewData);
        $pdf->setPaper('a4', 'landscape');

        return response()->stream(
            fn() => print($pdf->output()),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="laporan-keuangan.pdf"',
            ]
        );
    })->name('jurnal-keuangan.rekap');
});

// Route untuk testing halaman error premium
Route::get('/preview-404', function () {
    return view('errors.404');
});

Route::get('/preview-500', function () {
    return view('errors.500');
});
