<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * @implements FromCollection<int, Barang>
 * @implements WithMapping<Barang>
 */
class BarangExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    public function __construct(private readonly bool $template = false) {}

    /**
     * @return Enumerable<int, Barang>
     */
    public function collection(): Enumerable
    {
        if ($this->template) {
            return collect();
        }

        return Barang::query()
            ->with('kategori')
            ->orderBy('KodeItem')
            ->get();
    }

    public function map(mixed $barang): array
    {
        return [
            $barang->KodeItem,
            $barang->Barcode,
            $barang->SKU,
            $barang->NamaItem,
            $barang->kategori->nama_kategori ?? '',
            $barang->Merek,
            $barang->Rak,
            $barang->TipeItem,
            '',
            (float) $barang->HargaBeli,
            (float) $barang->HargaJual,
            $barang->Keterangan,
            0,
            0,
        ];
    }

    public function headings(): array
    {
        return [
            'KodeItem',
            'Barcode',
            'SKU',
            'NamaItem',
            'Jenis',
            'Merek',
            'Rak',
            'TipeItem',
            'Satuan',
            'HargaPokok',
            'HargaJual',
            'Keterangan',
            'Poin',
            'KomisiSales',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => '@',
            'C' => '@',
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
