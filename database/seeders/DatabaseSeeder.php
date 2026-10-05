<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
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
            LokasiSeeder::class,
        ]);
        
        $lokasi1 = Lokasi::where(
            'nama',
            'AlkadiSayur Sokka'
        )->firstOrFail();

        $lokasi2 = Lokasi::where(
            'nama',
            'AlkadiSayur Pemuda'
        )->firstOrFail();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make ('admin123'),
            'role' => 'superAdmin',
        ]);

        User::factory()->create([
            'name' => 'Admin Alkadi 1',
            'email' => 'adminalkadi1@example.com',
            'password' => Hash::make ('admin1'),
            'role' => 'admin',
            'lokasi_id' => $lokasi1->id,
        ]);

        User::factory()->create([
            'name' => 'Admin Alkadi 2',
            'email' => 'adminalkadi2@example.com',
            'password' => Hash::make ('admin2'),
            'role' => 'admin',
            'lokasi_id' => $lokasi2->id,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make ('password'),
            'role' => 'customer',
        ]);

        $this->call([
            KategoriSeeder::class,
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
