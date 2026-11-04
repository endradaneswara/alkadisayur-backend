<?php

namespace App\Http\Resources;

use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lokasi
 */
class LokasiResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'deskripsi' => $this->deskripsi,
            'link_gmaps' => $this->link_gmaps,
            'biaya_pengiriman' => $this->biaya_pengiriman,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
