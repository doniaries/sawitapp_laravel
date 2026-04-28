<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Settings\ManageSettings;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

// Route::get('/', function () {
//     return view('welcome');
// });

//langsung ke halaman login
// Route::get('/', function () {
//     return redirect('/admin');
// });

//production
Route::get('/', function () {
    $perusahaans = \App\Models\Perusahaan::where('is_active', true)
        ->select(['id', 'name', 'alamat', 'logo', 'telepon', 'slug'])
        ->get();
    return view('welcome', compact('perusahaans'));
});

Route::redirect('login', '/admin/login')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware(['auth', 'check.perusahaan'])->group(function () {
    // routes yang membutuhkan data perusahaan
});

// Route::get('transaksi-do/{id}/pdf', function ($id) {
//     $transaksi = \App\Models\TransaksiDo::findOrFail($id);
//     return $transaksi->generatePdf();
// })->name('transaksi-do.pdf');

use App\Http\Controllers\Web\JurnalKeuanganRekapController;
use App\Http\Controllers\Web\TransaksiDoPdfController;

// ... existing code ...

// Pastikan route PDF dalam middleware auth
Route::middleware(['auth'])->group(function () {
    Route::get('transaksi-do/{id}/pdf', TransaksiDoPdfController::class)->name('transaksi-do.pdf');
    Route::get('jurnal-keuangan/rekap', JurnalKeuanganRekapController::class)->name('jurnal-keuangan.rekap');
});

// Route untuk testing halaman error premium
Route::view('/preview-404', 'errors.404');
Route::view('/preview-500', 'errors.500');
