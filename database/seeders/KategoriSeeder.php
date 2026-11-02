<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            'sayuran',
            'buah',
            'bumbu dapur',
            'bahan pokok',
            'hewani',
            'makanan minuman',
            'frozen food',
            'lainnya',
        ];

        foreach ($kategori as $nama) {
            Kategori::create([
                'nama_kategori' => $nama,
            ]);
        }
    }
}
