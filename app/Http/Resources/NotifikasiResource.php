<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotifikasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'pesanan_id' => $this->pesanan_id,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'dibaca_pada' => $this->dibaca_pada,
            'sudah_dibaca' => $this->dibaca_pada !== null,
            'pesanan' => new PesananResource(
                $this->whenLoaded('pesanan')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
