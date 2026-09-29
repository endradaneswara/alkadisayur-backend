<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kategori_id' => $this->kategori_id,
            'nama_kategori' => $this->whenLoaded('kategori', fn() => $this->kategori?->nama_kategori),
            'KodeItem' => $this->KodeItem,
            'Barcode' => $this->Barcode,
            'SKU' => $this->SKU,
            'NamaItem' => $this->NamaItem,
            'Merek' => $this->Merek,
            'Stok' => $this->Stok,
            'Rak' => $this->Rak,
            'TipeItem' => $this->TipeItem,
            'HargaBeli' => $this->HargaBeli,
            'HargaJual' => $this->HargaJual,
            'Keterangan' => $this->Keterangan,
            'Foto' => $this->Foto,
            'Status' => $this->Status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
