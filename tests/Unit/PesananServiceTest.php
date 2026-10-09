<?php

use App\Models\Alamat;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $kategori = Kategori::factory()->create();
    $this->barang = Barang::factory()->create([
        'kategori_id' => $kategori->id,
        'NamaItem' => 'Kentang',
        'Stok' => 10,
        'HargaJual' => 4000,
        'Status' => 'tersedia',
    ]);
    $this->alamat = Alamat::factory()->create(['user_id' => $this->user->id]);
    $this->lokasi = Lokasi::create([
        'nama' => 'Gudang Service Pesanan',
        'latitude' => -6.21,
        'longitude' => 106.81,
        'biaya_pengiriman' => 7000,
    ]);
    $this->keranjang = Keranjang::create(['user_id' => $this->user->id]);
    $this->keranjang->items()->create([
        'barang_id' => $this->barang->id,
        'jumlah' => 2,
        'harga' => 4000,
    ]);
});

test('creating an order snapshots item names and decrements stock atomically', function () {
    $pesanan = app(PesananService::class)->store($this->user->id, [
        'alamat_id' => $this->alamat->id,
        'lokasi_toko_id' => $this->lokasi->id,
        'metode' => 'pesan_antar',
    ]);

    expect((int) $this->barang->fresh()->Stok)->toBe(8)
        ->and((float) $pesanan->total_harga)->toBe(8000.0)
        ->and((float) $pesanan->biaya_pengiriman)->toBe(7000.0)
        ->and($pesanan->items->first()->nama_barang)->toBe('Kentang')
        ->and($this->keranjang->items()->count())->toBe(0);
});

test('an order with insufficient stock leaves the order and stock unchanged', function () {
    $this->keranjang->items()->update(['jumlah' => 11]);

    expect(fn () => app(PesananService::class)->store($this->user->id, [
        'alamat_id' => $this->alamat->id,
        'lokasi_toko_id' => $this->lokasi->id,
        'metode' => 'pesan_antar',
    ]))->toThrow(ValidationException::class);

    expect(Pesanan::count())->toBe(0)
        ->and((int) $this->barang->fresh()->Stok)->toBe(10)
        ->and($this->keranjang->items()->count())->toBe(1);
});

test('cancelling an order restores each reserved unit exactly once', function () {
    $service = app(PesananService::class);
    $pesanan = $service->store($this->user->id, [
        'alamat_id' => $this->alamat->id,
        'lokasi_toko_id' => $this->lokasi->id,
        'metode' => 'pesan_antar',
    ]);

    $service->cancel($this->user->id, $pesanan->id);

    expect((int) $this->barang->fresh()->Stok)->toBe(10)
        ->and(Pesanan::findOrFail($pesanan->id)->status)->toBe('dibatalkan');

    expect(fn () => $service->cancel($this->user->id, $pesanan->id))
        ->toThrow(ValidationException::class);

    expect((int) $this->barang->fresh()->Stok)->toBe(10);
});
