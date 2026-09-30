<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
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
            'pesanan' => new PesananResource(
                $this->whenLoaded('pesanan')
            ),
        ];
    }
}