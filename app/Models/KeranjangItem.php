<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeranjangItem extends Model
{
    use HasFactory;

    protected $table = 'keranjang_item';

    protected $fillable = [
        'keranjang_id',
        'barang_id',
        'jumlah',
        'harga',
    ];

    protected function casts(): array
    {
        return [
            'keranjang_id' => 'integer',
            'barang_id' => 'integer',
            'jumlah' => 'integer',
            'harga' => 'decimal:2',
        ];
    }

    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
