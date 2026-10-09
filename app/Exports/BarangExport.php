<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BarangExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    public function __construct(private readonly bool $template = false) {}

    public function collection(): Collection
    {
        if ($this->template) {
            return collect();
        }

        return Barang::query()
            ->orderBy('KodeItem')
            ->get([
                'KodeItem',
                'Barcode',
                'SKU',
                'NamaItem',
                'kategori_id',
                'Merek',
                'Stok',
                'Rak',
                'TipeItem',
                'HargaBeli',
                'HargaJual',
                'Keterangan',
                'Foto',
                'Status',
            ]);
    }

    public function map(mixed $barang): array
    {
        if (! $barang instanceof Barang) {
            throw new \LogicException('Export barang menerima data dengan tipe yang tidak valid.');
        }

        return [
            $barang->KodeItem,
            $barang->Barcode,
            $barang->SKU,
            $barang->NamaItem,
            $barang->kategori_id,
            $barang->Merek,
            $barang->Stok,
            $barang->Rak,
            $barang->TipeItem,
            (float) $barang->HargaBeli,
            (float) $barang->HargaJual,
            $barang->Keterangan,
            $barang->Foto,
            $barang->Status,
        ];
    }

    public function headings(): array
    {
        return [
            'KodeItem',
            'Barcode',
            'SKU',
            'NamaItem',
            'kategori_id',
            'Merek',
            'Stok',
            'Rak',
            'TipeItem',
            'HargaBeli',
            'HargaJual',
            'Keterangan',
            'Foto',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => '@',
            'C' => '@',
            'G' => '0',
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
