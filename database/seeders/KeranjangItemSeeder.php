<?php

namespace Database\Seeders;

use App\Models\KeranjangItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KeranjangItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KeranjangItem::factory()->count(5)->create();
    }
}
