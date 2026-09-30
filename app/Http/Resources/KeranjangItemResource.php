<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeranjangItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barang_id' => $this->barang_id,
            'jumlah' => $this->jumlah,
            'harga' => $this->harga,
            'subtotal' => $this->jumlah * $this->harga,
            'barang' => new BarangResource(
                $this->whenLoaded('barang')
            ),
        ];
    }
}