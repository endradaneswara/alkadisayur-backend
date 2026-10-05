<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return Barang::select([
            'id',
            'kategori_id',
            'KodeItem',
            'Barcode',
            'SKU',
            'NamaItem',
            'Merek',
            'Stok',
            'Rak',
            'TipeItem',
            'HargaBeli',
            'HargaJual',
            'Keterangan',
            'foto',
            'status',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'kategori_id',
            'KodeItem',
            'Barcode',
            'SKU',
            'NamaItem',
            'Merek',
            'Stok',
            'Rak',
            'TipeItem',
            'HargaBeli',
            'HargaJual',
            'Keterangan',
            'foto',
            'status',
        ];
    }
}
