<?php

namespace App\Http\Resources;

use App\Models\PesananItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PesananItem
 */
class PesananItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pesanan_id' => $this->pesanan_id,
            'barang_id' => $this->barang_id,
            'nama_barang' => $this->nama_barang,
            'jumlah' => $this->jumlah,
            'harga' => $this->harga,
            'subtotal' => $this->subtotal,
            'barang' => $this->whenLoaded('barang', function () {
                return [
                    'NamaItem' => $this->barang->NamaItem,
                ];
            }),
        ];
    }
}
