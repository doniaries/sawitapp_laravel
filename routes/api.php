<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LogistikController;
use App\Http\Controllers\Api\OperasionalController;
use App\Http\Controllers\Api\PenjualController;
use App\Http\Controllers\Api\TransaksiDoController;
use App\Http\Controllers\Api\TambahSaldoController;
use App\Http\Controllers\Api\JurnalKeuanganController;
use App\Http\Controllers\Api\PekerjaController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Public Routes
 */
Route::post('/login', [AuthController::class, 'login']);
Route::get('/app-settings', [AppSettingsController::class, 'index']);
Route::get('/login', function () {
    return response()->json([
        'message' => 'Silakan gunakan metode POST untuk login.',
        'status' => 'error'
    ], 405);
});

/**
 * Protected Routes (Requires Sanctum Token)
 */
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/photo', [AuthController::class, 'updatePhoto']);

    // Multi-Tenancy (Perusahaan)
    Route::get('/perusahaans', [AuthController::class, 'getPerusahaans']);
    Route::post('/switch-perusahaan', [AuthController::class, 'switchPerusahaan']);
    Route::post('/perusahaan/logo', [AuthController::class, 'updateCompanyLogo']);

    // Transaksi DO
    Route::get('/transaksi-do', [TransaksiDoController::class, 'index']);
    Route::get('/transaksi-do/{id}', [TransaksiDoController::class, 'show']);
    Route::post('/transaksi-do', [TransaksiDoController::class, 'store']);

    // Tambah Saldo
    Route::get('/tambah-saldo', [TambahSaldoController::class, 'index']);
    Route::post('/tambah-saldo', [TambahSaldoController::class, 'store']);
    Route::get('/tambah-saldo/{id}', [TambahSaldoController::class, 'show']);
    Route::post('/tambah-saldo/{id}/approve', [TambahSaldoController::class, 'approve']);
    Route::post('/tambah-saldo/{id}/reject', [TambahSaldoController::class, 'reject']);

    // Penjual
    Route::get('/penjual', [PenjualController::class, 'index']);
    Route::get('/penjual/{id}', [PenjualController::class, 'show']);
    Route::post('/penjual', [PenjualController::class, 'store']);
    Route::put('/penjual/{id}', [PenjualController::class, 'update']);

    // Pekerja
    Route::get('/pekerja', [PekerjaController::class, 'index']);
    Route::get('/pekerja/{id}', [PekerjaController::class, 'show']);
    Route::post('/pekerja', [PekerjaController::class, 'store']);
    Route::put('/pekerja/{id}', [PekerjaController::class, 'update']);

    // Logistik
    Route::get('/supir', [LogistikController::class, 'supir']);
    Route::get('/supir/{id}', [LogistikController::class, 'showSupir']);
    Route::post('/supir', [LogistikController::class, 'storeSupir']);
    Route::put('/supir/{id}', [LogistikController::class, 'updateSupir']);
    
    Route::get('/kendaraan', [LogistikController::class, 'kendaraan']);
    Route::get('/kendaraan/{id}', [LogistikController::class, 'showKendaraan']);
    Route::post('/kendaraan', [LogistikController::class, 'storeKendaraan']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    // Operasional
    Route::get('/operasional', [OperasionalController::class, 'index']);
    Route::get('/operasional/{id}', [OperasionalController::class, 'show']);
    Route::post('/operasional', [OperasionalController::class, 'store']);

    // Jurnal Keuangan
    Route::get('/jurnal-keuangan', [JurnalKeuanganController::class, 'index']);

    // App Settings (Update)
    Route::post('/app-settings', [AppSettingsController::class, 'update']);
});
