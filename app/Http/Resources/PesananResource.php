<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id'=> $this->user_id,
            'nomor_order' => $this->nomor_order,
            'metode' => $this->metode,
            'status' => $this->status,
            'total_harga' => $this->total_harga,
            'biaya_pengiriman' => $this->biaya_pengiriman,
            'catatan' => $this->catatan,
            'tanggal_pemesanan' => $this->tanggal_pemesanan,
            'alamat' => new AlamatResource(
                $this->whenLoaded('alamat')
            ),
            'lokasi_toko' => new LokasiResource(
                $this->whenLoaded('lokasiToko')
            ),
            'items' => PesananItemResource::collection(
                $this->whenLoaded('items')
            ),
            'pembayaran' => new PembayaranResource(
                $this->whenLoaded('pembayaran')
            ),
        ];
    }
}