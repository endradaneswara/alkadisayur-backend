<?php

namespace Database\Factories;

use App\Models\Alamat;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'alamat_id' => Alamat::factory(),
            'lokasi_toko_id' => null,
            'nomor_order' => fake()->unique()->bothify('ORD-########'),
            'total_harga' => fake()->randomFloat(2, 10000, 5000000),
            'biaya_pengiriman' => fake()->randomFloat(2, 5000, 50000),
            'metode' => fake()->randomElement([
                'pesan_antar',
                'ambil_ditoko',
            ]),
            'status' => fake()->randomElement([
                'pending',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan',
            ]),
            'catatan' => fake()->optional()->sentence(),
            'tanggal_pemesanan' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
