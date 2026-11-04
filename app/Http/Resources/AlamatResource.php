<?php

namespace App\Http\Resources;

use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Alamat
 */
class AlamatResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nama_alamat' => $this->nama_alamat,
            'nama_penerima' => $this->nama_penerima,
            'no_hp' => $this->no_hp,
            'alamat_lengkap' => $this->alamat_lengkap,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
