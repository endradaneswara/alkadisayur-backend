<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function index(): array
    {
        return [
            'total_produk' => Barang::count(),
            'total_kategori' => Kategori::count(),
            'total_pengguna' => User::count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'total_customer' => User::where('role', 'customer')->count(),
            'total_pesanan' => Pesanan::count(),
            'pesanan_pending' => Pesanan::where('status', 'pending')->count(),
            'pesanan_diproses' => Pesanan::where('status', 'diproses')->count(),
            'pesanan_dikirim' => Pesanan::where('status', 'dikirim')->count(),
            'pesanan_selesai' => Pesanan::where('status', 'selesai')->count(),
            'pesanan_dibatalkan' => Pesanan::where('status', 'dibatalkan')->count(),
            'total_penjualan' => Pesanan::where('status', 'selesai')
                ->sum(DB::raw('total_harga + biaya_pengiriman')),
            'stok_menipis' => Barang::where('Stok', '<=', 3)->count(),
            'produk_tidak_tersedia' => Barang::where('Status', 'tidak_tersedia')->count(),
        ];
    }
}
