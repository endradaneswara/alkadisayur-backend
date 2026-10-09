<?php

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\User;
use App\Services\KeranjangService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $kategori = Kategori::factory()->create();
    $this->barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'Stok' => 8,
        'HargaJual' => 3500,
        'Status' => 'tersedia',
    ]);
});

test('adding an existing cart item increases its quantity using the current product price', function () {
    $service = app(KeranjangService::class);

    $service->store($this->user->id, [
        'barang_id' => $this->barang->id,
        'jumlah' => 2,
    ]);
    $this->barang->update(['HargaJual' => 4000]);
    $keranjang = $service->store($this->user->id, [
        'barang_id' => $this->barang->id,
        'jumlah' => 3,
    ]);

    expect($keranjang->items)->toHaveCount(1)
        ->and((int) $keranjang->items->first()->jumlah)->toBe(5)
        ->and((float) $keranjang->items->first()->harga)->toBe(4000.0);
});

test('cart quantity cannot exceed stock or include unavailable products', function () {
    $service = app(KeranjangService::class);

    expect(fn () => $service->store($this->user->id, [
        'barang_id' => $this->barang->id,
        'jumlah' => 9,
    ]))->toThrow(ValidationException::class);

    $this->barang->update(['Status' => 'tidak_tersedia']);

    expect(fn () => $service->store($this->user->id, [
        'barang_id' => $this->barang->id,
        'jumlah' => 1,
    ]))->toThrow(ValidationException::class);

    expect(Keranjang::where('user_id', $this->user->id)->exists())->toBeFalse();
});

test('updating and clearing a cart only changes its items', function () {
    $service = app(KeranjangService::class);
    $keranjang = $service->store($this->user->id, [
        'barang_id' => $this->barang->id,
        'jumlah' => 2,
    ]);
    $item = $keranjang->items->first();

    $service->update($this->user->id, $item->id, ['jumlah' => 4]);
    $service->clear($this->user->id);

    expect($keranjang->fresh()->items()->count())->toBe(0)
        ->and((int) $this->barang->fresh()->Stok)->toBe(8);
});
