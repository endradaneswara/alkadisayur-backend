<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PesananItem>
 */
class PesananItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $harga = fake()->randomFloat(2, 1000, 1000000);
        $jumlah = fake()->numberBetween(1, 10);

        return [
            'pesanan_id' => Pesanan::factory(),
            'barang_id' => Barang::factory(),
            'nama_barang' => fake()->words(3, true),
            'harga' => $harga,
            'jumlah' => $jumlah,
            'subtotal' => $harga * $jumlah,
        ];
    }
}
