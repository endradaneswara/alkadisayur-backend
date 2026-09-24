<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'kategori_id',
        'KodeItem',
        'Barcode',
        'SKU',
        'NamaItem',
        'Merek',
        'Stok',
        'Rak',
        'TipeItem',
        'HargaBeli',
        'HargaJual',
        'Keterangan',
        'foto',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'kategori_id' => 'integer',
            'Stok' => 'integer',
            'HargaBeli' => 'decimal:2',
            'HargaJual' => 'decimal:2',
        ];
    }
}
