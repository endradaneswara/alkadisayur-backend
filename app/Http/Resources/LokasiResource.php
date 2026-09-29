<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LokasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'deskripsi' => $this->deskripsi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}