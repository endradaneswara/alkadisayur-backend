<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ringkasan' => [
                'total_produk' => $this['total_produk'],
                'total_kategori' => $this['total_kategori'],
                'total_pengguna' => $this['total_pengguna'],
                'total_pesanan' => $this['total_pesanan'],
                'total_penjualan' => $this['total_penjualan'],
            ],

            'pengguna' => [
                'total' => $this['total_pengguna'],
                'admin' => $this['total_admin'],
                'customer' => $this['total_customer'],
            ],

            'pesanan' => [
                'total' => $this['total_pesanan'],
                'pending' => $this['pesanan_pending'],
                'diproses' => $this['pesanan_diproses'],
                'dikirim' => $this['pesanan_dikirim'],
                'selesai' => $this['pesanan_selesai'],
                'dibatalkan' => $this['pesanan_dibatalkan'],
            ],

            'stok' => [
                'stok_menipis' => $this['stok_menipis'],
                'produk_tidak_tersedia' => $this['produk_tidak_tersedia'],
            ],
        ];
    }
}
