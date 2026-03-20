<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LogistikController;
use App\Http\Controllers\Api\OperasionalController;
use App\Http\Controllers\Api\PenjualController;
use App\Http\Controllers\Api\TransaksiDoController;
use App\Http\Controllers\Api\PengajuanDanaController;
use App\Http\Controllers\Api\JurnalKeuanganController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Public Routes
 */
Route::post('/login', [AuthController::class, 'login']);
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

    // Transaksi DO
    Route::get('/transaksi-do', [TransaksiDoController::class, 'index']);
    Route::get('/transaksi-do/{id}', [TransaksiDoController::class, 'show']);
    Route::post('/transaksi-do', [TransaksiDoController::class, 'store']);

    // Pengajuan Dana
    Route::get('/pengajuan-dana', [PengajuanDanaController::class, 'index']);
    Route::get('/pengajuan-dana/{id}', [PengajuanDanaController::class, 'show']);
    Route::post('/pengajuan-dana', [PengajuanDanaController::class, 'store']);
    Route::post('/pengajuan-dana/{id}/approve', [PengajuanDanaController::class, 'approve']);
    Route::post('/pengajuan-dana/{id}/reject', [PengajuanDanaController::class, 'reject']);

    // Penjual
    Route::get('/penjual', [PenjualController::class, 'index']);
    Route::get('/penjual/{id}', [PenjualController::class, 'show']);
    Route::post('/penjual', [PenjualController::class, 'store']);

    // Logistik
    Route::get('/supir', [LogistikController::class, 'supir']);
    Route::get('/supir/{id}', [LogistikController::class, 'showSupir']);
    Route::post('/supir', [LogistikController::class, 'storeSupir']);
    
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
});
