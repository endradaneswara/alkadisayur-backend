<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Barang;

/**
 * @mixin Barang
 */

class BarangCustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kategori_id' => $this->kategori_id,
            'nama_kategori' => $this->whenLoaded('kategori', fn() => $this->kategori?->nama_kategori),
            'NamaItem' => $this->NamaItem,
            'Merek' => $this->Merek,
            'TipeItem' => $this->TipeItem,
            'HargaJual' => $this->HargaJual,
            'Keterangan' => $this->Keterangan,
            'Foto' => $this->Foto,
            'Status' => $this->Status,
        ];
    }
}
