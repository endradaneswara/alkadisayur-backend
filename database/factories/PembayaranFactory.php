<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pesanan_id' => Pesanan::factory(),
            'metode_pembayaran' => fake()->randomElement([
                'QRIS',
                'Transfer',
                'COD',
            ]),
            'status' => fake()->randomElement([
                'pending',
                'berhasil',
                'gagal',
                'expired',
            ]),
            'jumlah' => fake()->randomFloat(2, 1000, 50000),
            'transaksi_id' => fake()->uuid(),
            'tanggal_pembayaran' => fake()->dateTime(),
        ];
    }
}
