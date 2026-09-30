<?php

use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

// Login dengan Google
Route::get('/auth/google', [AuthController::class, 'redirectGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'callbackGoogle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('kategori', KategoriController::class);
    Route::get('/kategori/{id}/barang', [KategoriController::class, 'barang']);
    Route::apiResource('barang', BarangController::class);
    Route::apiResource('lokasi', LokasiController::class);
    Route::apiResource('alamat', AlamatController::class);

    Route::get('/keranjang', [KeranjangController::class, 'index']);
    Route::post('/keranjang/items', [KeranjangController::class, 'store']);
    Route::put('/keranjang/items/{id}', [KeranjangController::class, 'update']);
    Route::delete('/keranjang/items/{id}', [KeranjangController::class, 'destroy']);
    Route::delete('/keranjang', [KeranjangController::class, 'clear']);

    Route::apiResource('pesanan', PesananController::class);
    Route::apiResource('pembayaran', PembayaranController::class)
            ->only(['index', 'store', 'show', 'update']);
    Route::apiResource('ulasan', UlasanController::class);

    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show']);
    Route::put('/notifikasi/{id}/read', [NotifikasiController::class, 'read']);
    Route::put('/notifikasi/read-all', [NotifikasiController::class, 'readAll']);
    Route::delete('/notifikasi/{id}', [NotifikasiController::class, 'destroy']);
});
