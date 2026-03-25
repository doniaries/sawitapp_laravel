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
});

// Route untuk testing halaman error premium
Route::get('/preview-404', function () {
    return view('errors.404');
});

Route::get('/preview-500', function () {
    return view('errors.500');
});
