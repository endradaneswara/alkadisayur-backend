<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kategori>
 */
class KategoriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->randomElement([
                'sayuran',
                'buah',
                'bumbu dapur',
                'bahan pokok',
                'hewani',
                'makanan minuman',
                'frozen food',
                'lainnya',
            ]),
        ];
    }
}
