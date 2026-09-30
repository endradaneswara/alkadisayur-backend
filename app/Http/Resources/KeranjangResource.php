<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeranjangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => KeranjangItemResource::collection(
                $this->whenLoaded('items')
            ),
            'total_item' => $this->whenLoaded(
                'items',
                fn () => $this->items->sum('jumlah')
            ),
            'total_harga' => $this->whenLoaded(
                'items',
                fn () => $this->items->sum(
                    fn ($item) => $item->jumlah * $item->harga
                )
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}