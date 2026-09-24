<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barang>
 */
class BarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_id' => Kategori::inRandomOrder()->first()->id,
            'KodeItem' => fake()->unique()->numerify('######'),
            'Barcode' => fake()->unique()->numerify('################'),
            'SKU' => fake()->unique()->numerify('######'),
            'NamaItem' => fake()->words(3, true),
            'Merek' => fake()->company(),
            'Stok' => fake()->numberBetween(0, 100),
            'Rak' => fake()->bothify('A-##'),
            'TipeItem' => fake()->bothify('INV-####'),
            'HargaBeli' => fake()->randomFloat(2, 1000, 1000000),
            'HargaJual' => fake()->randomFloat(2, 1000, 1500000),
            'Keterangan' => fake()->sentence(),
            'foto' => fake()->imageUrl(),
            'status' => fake()->randomElement([
                'tersedia',
                'tidak tersedia',
            ]),
        ];
    }
}
