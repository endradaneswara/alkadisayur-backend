<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            Barang::updateOrCreate(
                [
                    'Barcode' => $row['barcode'],
                ],
                [
                    'kategori_id' => $row['kategori_id'],
                    'KodeItem' => $row['kodeitem'],
                    'SKU' => $row['sku'],
                    'NamaItem' => $row['namaitem'],
                    'Merek' => $row['merek'] ?? null,
                    'Stok' => $row['stok'] ?? 0,
                    'Rak' => $row['rak'] ?? null,
                    'TipeItem' => $row['tipeitem'] ?? null,
                    'HargaBeli' => $row['hargabeli'],
                    'HargaJual' => $row['hargajual'],
                    'Keterangan' => $row['keterangan'] ?? null,
                    'foto' => $row['foto'] ?? null,
                    'status' => $row['status'] ?? 'tersedia',
                ]
            );
        }
    }
}

