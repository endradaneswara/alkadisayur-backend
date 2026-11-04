<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lokasi::create([
            'nama' => 'Alkadi Sokka',
            'latitude' => -7.678,
            'longitude' => 109.653,
            'deskripsi' => 'Alkadi Fresh Market cabang Sokka Kebumen.',
        ]);

        Lokasi::create([
            'nama' => 'Alkadi Pemuda',
            'latitude' => -7.607,
            'longitude' => 109.514,
            'deskripsi' => 'Alkadi Fresh Market cabang Pemuda Kebumen.',
        ]);
    }
}
