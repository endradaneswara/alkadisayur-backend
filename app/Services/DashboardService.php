<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Rentang grafik yang didukung, dalam jumlah hari.
     *
     * @var array<string, int>
     */
    private const RENTANG_GRAFIK = [
        '1h' => 1,
        '7h' => 7,
        '30h' => 30,
    ];

    /**
     * Ringkasan statistik dashboard admin/super admin beserta
     * grafik penjualan harian dan daftar pesanan terbaru.
     *
     * Data pesanan dan penjualan admin dibatasi pada lokasi tokonya.
     * Super admin dapat memfilter berdasarkan lokasi toko tertentu atau melihat semua cabang.
     *
     * @param  string  $rentang  '1h' | '7h' | '30h'
     * @return array<string, mixed>
     */
    public function index(User $user, string $rentang = '7h', ?int $lokasiId = null): array
    {
        $hari = self::RENTANG_GRAFIK[$rentang] ?? 7;
        $pesananQuery = $this->pesananQuery($user, $lokasiId);

        $data = [
            'total_produk' => Barang::count(),
            'total_kategori' => Kategori::count(),
            'total_pengguna' => User::count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'total_customer' => User::where('role', 'customer')->count(),
            'total_pesanan' => (clone $pesananQuery)->count(),
            'pesanan_pending' => (clone $pesananQuery)->where('status', 'pending')->count(),
            'pesanan_diproses' => (clone $pesananQuery)->where('status', 'diproses')->count(),
            'pesanan_dikirim' => (clone $pesananQuery)->where('status', 'dikirim')->count(),
            'pesanan_selesai' => (clone $pesananQuery)->where('status', 'selesai')->count(),
            'pesanan_dibatalkan' => (clone $pesananQuery)->where('status', 'dibatalkan')->count(),
            'total_penjualan' => (clone $pesananQuery)
                ->where('status', 'selesai')
                ->sum(DB::raw('total_harga + biaya_pengiriman')),
            'stok_menipis' => Barang::where('Stok', '<=', 3)->count(),
            'produk_tidak_tersedia' => Barang::where('Status', 'tidak_tersedia')->count(),
            'grafik_penjualan' => $this->grafikPenjualan($user, $hari, $lokasiId),
            'pesanan_terbaru' => $this->pesananTerbaru($user, $lokasiId),
            'lokasi_terpilih' => $lokasiId
                ? Lokasi::find($lokasiId)
                : ($user->role === 'admin' && $user->lokasi_id ? Lokasi::find($user->lokasi_id) : null),
        ];

        if ($user->role === 'superAdmin') {
            $data['penjualan_per_cabang'] = Lokasi::query()
                ->withCount(['pesanan as total_pesanan' => function ($q) {
                    $q->where('status', 'selesai');
                }])
                ->withSum(['pesanan as total_penjualan' => function ($q) {
                    $q->where('status', 'selesai');
                }], DB::raw('total_harga + biaya_pengiriman'))
                ->get()
                ->map(function ($lokasi) {
                    return [
                        'id' => $lokasi->id,
                        'nama' => $lokasi->nama,
                        'total_pesanan' => (int) $lokasi->total_pesanan,
                        'total_penjualan' => (float) ($lokasi->total_penjualan ?? 0),
                    ];
                })
                ->values()
                ->all();
        }

        return $data;
    }

    private function pesananQuery(User $user, ?int $lokasiId = null): Builder
    {
        $query = Pesanan::query();

        if ($user->role === 'admin') {
            if ($user->lokasi_id === null) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('lokasi_toko_id', $user->lokasi_id);
        } elseif ($user->role === 'superAdmin' && $lokasiId !== null) {
            $query->where('lokasi_toko_id', $lokasiId);
        }

        return $query;
    }

    /**
     * Seri harian penjualan (pesanan selesai) untuk grafik.
     *
     * @return array<int, array{tanggal: string, total_penjualan: float, total_pesanan: int}>
     */
    private function grafikPenjualan(User $user, int $hari, ?int $lokasiId = null): array
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();

        $penjualan = $this->pesananQuery($user, $lokasiId)
            ->where('status', 'selesai')
            ->whereBetween('tanggal_pemesanan', [$mulai, now()->endOfDay()])
            ->selectRaw(
                'DATE(tanggal_pemesanan) AS tanggal, '
                .'COALESCE(SUM(total_harga + biaya_pengiriman), 0) AS total_penjualan, '
                .'COUNT(*) AS total_pesanan'
            )
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $grafik = [];
        for ($i = 0; $i < $hari; $i++) {
            $tanggal = $mulai->copy()->addDays($i)->format('Y-m-d');
            $data = $penjualan->get($tanggal);

            $grafik[] = [
                'tanggal' => $tanggal,
                'total_penjualan' => (float) ($data->total_penjualan ?? 0),
                'total_pesanan' => (int) ($data->total_pesanan ?? 0),
            ];
        }

        return $grafik;
    }

    private function pesananTerbaru(User $user, ?int $lokasiId = null): Collection
    {
        return $this->pesananQuery($user, $lokasiId)
            ->with([
                'user',
                'alamat',
                'lokasiToko',
                'items.barang',
                'pembayaran',
            ])
            ->latest('tanggal_pemesanan')
            ->limit(5)
            ->get();
    }
}
