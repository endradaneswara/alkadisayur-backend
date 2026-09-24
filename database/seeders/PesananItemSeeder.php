<?php

namespace Database\Seeders;

use App\Models\PesananItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PesananItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PesananItem::factory()->count(5)->create();
    }
}
