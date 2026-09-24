<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\KeranjangItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KeranjangItem>
 */
class KeranjangItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'keranjang_id' => Keranjang::factory(),
            'barang_id' => Barang::factory(),
            'jumlah' => fake()->numberBetween(1, 10),
            'harga' => fake()->randomFloat(2, 1000, 50000),
        ];
    }
}
