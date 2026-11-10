<?php

namespace App\Http\Resources;

use App\Models\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Keranjang
 */
class KeranjangResource extends JsonResource
{
    /** @return array<string, mixed> */
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
                fn () => $this->items->count()
            ),
            'total_harga' => $this->whenLoaded(
                'items',
                fn () => $this->items->sum(
                    fn (mixed $item): float => (float) $item->jumlah * (float) $item->harga
                )
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
