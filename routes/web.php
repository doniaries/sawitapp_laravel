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
