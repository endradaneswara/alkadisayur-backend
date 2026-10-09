<?php

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PembayaranService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('payment creation charges the order total and delivery fee', function () {
    $user = User::factory()->create();
    $pesanan = Pesanan::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total_harga' => 30000,
        'biaya_pengiriman' => 8000,
    ]);

    $pembayaran = app(PembayaranService::class)->store($user->id, [
        'pesanan_id' => $pesanan->id,
        'metode_pembayaran' => 'Transfer',
    ]);

    expect((float) $pembayaran->jumlah)->toBe(38000.0)
        ->and($pembayaran->status)->toBe('pending')
        ->and($pembayaran->transaksi_id)->toStartWith('TRX-');
});

test('cancelled orders cannot be paid and an order cannot have duplicate payments', function () {
    $user = User::factory()->create();
    $cancelledOrder = Pesanan::factory()->create([
        'user_id' => $user->id,
        'status' => 'dibatalkan',
    ]);

    expect(fn () => app(PembayaranService::class)->store($user->id, [
        'pesanan_id' => $cancelledOrder->id,
        'metode_pembayaran' => 'QRIS',
    ]))->toThrow(ValidationException::class);

    $activeOrder = Pesanan::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
    $service = app(PembayaranService::class);
    $service->store($user->id, [
        'pesanan_id' => $activeOrder->id,
        'metode_pembayaran' => 'QRIS',
    ]);

    expect(fn () => $service->store($user->id, [
        'pesanan_id' => $activeOrder->id,
        'metode_pembayaran' => 'Transfer',
    ]))->toThrow(ValidationException::class);

    expect(Pembayaran::where('pesanan_id', $activeOrder->id)->count())->toBe(1);
});

test('a customer cannot create payment for another customers order', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $pesanan = Pesanan::factory()->create(['user_id' => $anotherUser->id]);

    expect(fn () => app(PembayaranService::class)->store($user->id, [
        'pesanan_id' => $pesanan->id,
        'metode_pembayaran' => 'COD',
    ]))->toThrow(ModelNotFoundException::class);
});
