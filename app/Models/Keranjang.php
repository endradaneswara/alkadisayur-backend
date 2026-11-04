<?php

namespace App\Models;

use Database\Factories\KeranjangFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, KeranjangItem> $items
 */
class Keranjang extends Model
{
    /** @use HasFactory<KeranjangFactory> */
    use HasFactory;

    protected $table = 'keranjang';

    protected $fillable = [
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<KeranjangItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(KeranjangItem::class, 'keranjang_id');
    }
}
