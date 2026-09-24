<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lokasi::create([
        'nama' => 'AlkadiSayur Sokka',
        'latitude' => -7.678,
        'longitude' => 109.653,
        'deskripsi' => 'Toko AlkadiSayur cabang Kebumen.',
    ]);

        Lokasi::create([
        'nama' => 'AlkadiSayur Pemuda',
        'latitude' => -7.607,
        'longitude' => 109.514,
        'deskripsi' => 'Toko AlkadiSayur cabang Gombong.',
    ]);
    }
}
