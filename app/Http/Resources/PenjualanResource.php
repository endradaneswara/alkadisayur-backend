<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor_order' => $this->nomor_order,
            'pelanggan' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'nama' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'metode' => $this->metode,
            'total_harga' => $this->total_harga,
            'biaya_pengiriman' => $this->biaya_pengiriman,
            'total_pendapatan' =>
                (float) $this->total_harga +
                (float) $this->biaya_pengiriman,
            'tanggal_pemesanan' => $this->tanggal_pemesanan,
            'items' => PesananItemResource::collection(
                $this->whenLoaded('items')
            ),
            'pembayaran' => new PembayaranResource(
                $this->whenLoaded('pembayaran')
            ),
            'alamat' => new AlamatResource(
                $this->whenLoaded('alamat')
            ),
            'lokasi_toko' => new LokasiResource(
                $this->whenLoaded('lokasiToko')
            ),
        ];
    }
}
