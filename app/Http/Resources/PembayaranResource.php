<?php

namespace App\Http\Resources;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pembayaran
 */
class PembayaranResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pesanan_id' => $this->pesanan_id,
            'metode_pembayaran' => $this->metode_pembayaran,
            'status' => $this->status,
            'jumlah' => $this->jumlah,
            'transaksi_id' => $this->transaksi_id,
            'tanggal_pembayaran' => $this->tanggal_pembayaran,
            'pesanan' => $this->whenLoaded('pesanan', function () {
                return [
                    'id' => $this->pesanan->id,
                    'nomor_order' => $this->pesanan->nomor_order,
                    'status' => $this->pesanan->status,
                ];
            }),
        ];
    }
}
