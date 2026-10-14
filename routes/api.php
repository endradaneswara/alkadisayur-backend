<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])->name('login')
    ->middleware('throttle:5,1');

Route::post('/forgot-password', [PasswordController::class, 'forgot'])
    ->middleware('throttle:5,1');

Route::post('/reset-password', [PasswordController::class, 'reset'])
    ->middleware('throttle:5,1');

Route::get('/auth/google', [AuthController::class, 'redirectGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'callbackGoogle']);

Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/kategori/{id}', [KategoriController::class, 'show'])
    ->whereNumber('id');
Route::get('/kategori/{id}/barang', [KategoriController::class, 'barang'])
    ->whereNumber('id');

Route::get('/barang', [BarangController::class, 'index']);
Route::get('/barang/{id}', [BarangController::class, 'show'])
    ->whereNumber('id');

Route::get('/lokasi', [LokasiController::class, 'index']);
Route::get('/lokasi/{id}', [LokasiController::class, 'show'])
    ->whereNumber('id');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('throttle:10,1');

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [PasswordController::class, 'update'])
        ->middleware('throttle:5,1');

    Route::middleware('role:customer|admin|superAdmin')->group(function () {
        Route::apiResource('alamat', AlamatController::class);

        Route::get('/keranjang', [KeranjangController::class, 'index']);
        Route::post('/keranjang/items', [KeranjangController::class, 'store']);
        Route::put('/keranjang/items/{id}', [KeranjangController::class, 'update'])
            ->whereNumber('id');
        Route::delete('/keranjang/items/{id}', [KeranjangController::class, 'destroy'])
            ->whereNumber('id');
        Route::delete('/keranjang', [KeranjangController::class, 'clear']);

        Route::get('/pesanan', [PesananController::class, 'index']);
        Route::post('/pesanan', [PesananController::class, 'store']);
        Route::get('/pesanan/{id}', [PesananController::class, 'show'])
            ->whereNumber('id');
        Route::delete('/pesanan/{id}', [PesananController::class, 'destroy'])
            ->whereNumber('id');

        Route::apiResource('pembayaran', PembayaranController::class)
            ->only(['index', 'store', 'show']);

        Route::apiResource('ulasan', UlasanController::class);

        Route::get('/notifikasi', [NotifikasiController::class, 'index']);
        Route::put('/notifikasi/read-all', [NotifikasiController::class, 'readAll']);
        Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show'])
            ->whereNumber('id');
        Route::put('/notifikasi/{id}/read', [NotifikasiController::class, 'read'])
            ->whereNumber('id');
        Route::delete('/notifikasi/{id}', [NotifikasiController::class, 'destroy'])
            ->whereNumber('id');
    });

    Route::middleware('role:admin|superAdmin')->group(function () {
        Route::get('/admin/pesanan', [PesananController::class, 'adminIndex']);
        Route::get('/admin/pesanan/{id}', [PesananController::class, 'adminShow'])
            ->whereNumber('id');
        Route::put('/admin/pesanan/{id}/status', [PesananController::class, 'updateStatus'])
            ->whereNumber('id');

        Route::get('/admin/penjualan', [PenjualanController::class, 'index']);
        Route::get('/admin/penjualan/summary', [PenjualanController::class, 'summary']);
        Route::get('/admin/penjualan/{id}', [PenjualanController::class, 'show'])
            ->whereNumber('id');

        Route::get('/admin/notifikasi', [NotifikasiController::class, 'index']);
        Route::put('/admin/notifikasi/read-all', [NotifikasiController::class, 'readAll']);
        Route::get('/admin/notifikasi/{id}', [NotifikasiController::class, 'show'])
            ->whereNumber('id');
        Route::put('/admin/notifikasi/{id}/read', [NotifikasiController::class, 'read'])
            ->whereNumber('id');
        Route::delete('/admin/notifikasi/{id}', [NotifikasiController::class, 'destroy'])
            ->whereNumber('id');

        Route::apiResource('pembayaran', PembayaranController::class)
            ->only(['update']);
    });

    Route::middleware('role:superAdmin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('kategori', KategoriController::class)
            ->only(['store', 'update', 'destroy']);

        Route::apiResource('barang', BarangController::class)
            ->only(['store', 'update', 'destroy']);

        Route::apiResource('lokasi', LokasiController::class)
            ->only(['store', 'update', 'destroy']);
        Route::put('/lokasi/{id}/biayaPengiriman', [LokasiController::class, 'updateBiayaPengiriman'])
            ->whereNumber('id');

        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show'])
            ->whereNumber('id');
        Route::put('/users/{id}/role', [UserController::class, 'updateRole'])
            ->whereNumber('id');

        Route::get('/penjualan', [PenjualanController::class, 'index']);
        Route::get('/penjualan/summary', [PenjualanController::class, 'summary']);
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])
            ->whereNumber('id');

        Route::get('/data/export', [DataController::class, 'export']);
        Route::get('/data/template', [DataController::class, 'template']);
        Route::post('/data/import', [DataController::class, 'import']);
    });
});
