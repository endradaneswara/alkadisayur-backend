<?php

namespace Database\Factories;

use App\Models\Alamat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alamat>
 */
class AlamatFactory extends Factory
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
            'nama_alamat' => fake()->randomElement(['Rumah', 'Kantor', 'Apartemen']),
            'nama_penerima' => fake()->name(),
            'no_hp' => fake()->numerify('08##########'),
            'alamat_lengkap' => fake()->address(),
        ];
    }
}
