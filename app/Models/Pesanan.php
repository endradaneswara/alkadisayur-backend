<?php

namespace App\Models;

use Database\Factories\PesananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read User|null $user
 */
class Pesanan extends Model
{
    /** @use HasFactory<PesananFactory> */
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'alamat_id',
        'lokasi_toko_id',
        'nomor_order',
        'total_harga',
        'biaya_pengiriman',
        'metode',
        'status',
        'catatan',
        'tanggal_pemesanan',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'alamat_id' => 'integer',
            'total_harga' => 'decimal:2',
            'biaya_pengiriman' => 'decimal:2',
            'tanggal_pemesanan' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Alamat, $this> */
    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class);
    }

    /** @return HasOne<Pembayaran, $this> */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    /** @return BelongsTo<Lokasi, $this> */
    public function lokasi(): BelongsTo
    {
        return $this->lokasiToko();
    }

    /** @return BelongsTo<Lokasi, $this> */
    public function lokasiToko(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_toko_id');
    }

    /** @return HasMany<PesananItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }

    /** @return HasMany<PesananItem, $this> */
    public function pesananItem(): HasMany
    {
        return $this->items();
    }
}
