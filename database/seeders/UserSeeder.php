<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan kedua lokasi tersedia di database.
        $lokasi1 = Lokasi::where(
            'nama',
            'Alkadi Sokka'
        )->firstOrFail();

        $lokasi2 = Lokasi::where(
            'nama',
            'Alkadi Pemuda'
        )->firstOrFail();

        // Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Superadmin123'),
                'role' => 'superAdmin',
            ]
        );

        // Admin Alkadi Sokka
        User::firstOrCreate(
            ['email' => 'adminsokka1@example.com'],
            [
                'name' => 'Admin Sokka 1',
                'password' => Hash::make('Admin1'),
                'role' => 'admin',
                'lokasi_id' => $lokasi1->id,
            ]
        );

        // Admin Alkadi Pemuda
        User::firstOrCreate(
            ['email' => 'adminalkadi2@example.com'],
            [
                'name' => 'Admin Alkadi 2',
                'password' => Hash::make('admin2'),
                'role' => 'admin',
                'lokasi_id' => $lokasi2->id,
            ]
        );
    }
}
