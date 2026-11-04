<?php

namespace App\Models;

use Database\Factories\KeranjangItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Barang|null $barang
 */
class KeranjangItem extends Model
{
    /** @use HasFactory<KeranjangItemFactory> */
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

    /** @return BelongsTo<Keranjang, $this> */
    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class);
    }

    /** @return BelongsTo<Barang, $this> */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
