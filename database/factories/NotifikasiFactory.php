<?php

namespace Database\Factories;

use App\Models\Notifikasi;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notifikasi>
 */
class NotifikasiFactory extends Factory
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
            'pesanan_id' => Pesanan::factory(),
            'judul' => fake()->randomElement([
                'pesanan_baru',
                'pesanan_diproses',
                'pesanan_dikirim',
                'pesanan_selesai',
            ]),
            'pesan' => fake()->randomElement([
                'Pesanan baru telah masuk dan menunggu diproses.',
                'Pesanan sedang diproses oleh admin.',
                'Pesanan telah dikirim kepada customer.',
                'Pesanan telah selesai.',
            ]),
            'dibaca_pada' => null,
        ];
    }
}
