<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pesanan_id' => $this->pesanan_id,
            'barang_id' => $this->barang_id,
            'jumlah' => $this->jumlah,
            'harga' => $this->harga,
            'subtotal' => $this->subtotal,
            'barang' => new BarangResource(
                $this->whenLoaded('barang')
            ),
        ];
    }
}