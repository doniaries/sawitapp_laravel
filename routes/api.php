<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LogistikController;
use App\Http\Controllers\Api\OperasionalController;
use App\Http\Controllers\Api\PenjualController;
use App\Http\Controllers\Api\TransaksiDoController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Public Routes
 */
Route::post('/login', [AuthController::class, 'login']);

/**
 * Protected Routes (Requires Sanctum Token)
 */
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Transaksi DO
    Route::get('/transaksi-do', [TransaksiDoController::class, 'index']);
    Route::get('/transaksi-do/{id}', [TransaksiDoController::class, 'show']);

    // Penjual
    Route::get('/penjual', [PenjualController::class, 'index']);

    // Logistik
    Route::get('/supir', [LogistikController::class, 'supir']);
    Route::get('/kendaraan', [LogistikController::class, 'kendaraan']);

    // Operasional
    Route::get('/operasional', [OperasionalController::class, 'index']);
});
