<?php

namespace App\Http\Resources;

use App\Models\KeranjangItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin KeranjangItem
 */
class KeranjangItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barang_id' => $this->barang_id,
            'nama_barang' => $this->barang?->NamaItem,
            'jumlah' => $this->jumlah,
            'harga' => $this->harga,
            'subtotal' => $this->jumlah * $this->harga,
        ];
    }
}
