<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            KategoriSeeder::class,
            UserSeeder::class,
            BarangSeeder::class,
            KeranjangSeeder::class,
            KeranjangItemSeeder::class,
            AlamatSeeder::class,
            PesananSeeder::class,
            PesananItemSeeder::class,
            PembayaranSeeder::class,
            UlasanSeeder::class,
            NotifikasiSeeder::class,
        ]);
    }
}
