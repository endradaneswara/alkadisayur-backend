<?php

use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LokasiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

// Login dengan Google
Route::get('/auth/google', [AuthController::class, 'redirectGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'callbackGoogle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('kategori', KategoriController::class);
    Route::apiResource('barang', BarangController::class);
    Route::get('/kategori/{id}/barang', [KategoriController::class, 'barang']);
    Route::apiResource('lokasi', LokasiController::class);


});