<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'pesanan_id',
        'metode_pembayaran',
        'status',
        'jumlah',
        'transaksi_id',
        'tanggal_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'pesanan_id' => 'integer',
            'jumlah' => 'decimal:2',
            'tanggal_pembayaran' => 'datetime',
        ];
    }

    /**
     * Get the order associated with this payment.
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }
}
