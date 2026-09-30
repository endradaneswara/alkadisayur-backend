<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function lokasi(): BelongsTo
    {
        return $this->BelongsTo(Lokasi::class);
    }

    public function pesananItem(): HasMany
    {
        return $this->HasMany(PesananItem::class);
    }

}
