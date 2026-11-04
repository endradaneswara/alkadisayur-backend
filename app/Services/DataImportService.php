<?php

namespace App\Services;

use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class DataImportService
{
    /**
     * @var list<string>
     */
    private const LEGACY_HEADERS = [
        'kodeitem',
        'barcode',
        'sku',
        'namaitem',
        'kategori_id',
        'merek',
        'stok',
        'rak',
        'tipeitem',
        'hargabeli',
        'hargajual',
        'keterangan',
        'foto',
        'status',
    ];

    /**
     * Required conceptual columns for the new Excel format and their acceptable header aliases.
     *
     * @var array<string, list<string>>
     */
    private const NEW_FORMAT_COLUMNS = [
        'KodeItem' => ['kodeitem', 'kode_item'],
        'Barcode' => ['barcode'],
        'SKU' => ['sku'],
        'NamaItem' => ['namaitem', 'nama_item'],
        'Jenis' => ['jenis', 'kategori', 'nama_kategori'],
        'Merek' => ['merek', 'merk'],
        'Rak' => ['rak'],
        'TipeItem' => ['tipeitem', 'tipe_item'],
        'Satuan' => ['satuan'],
        'HargaPokok' => ['hargapokok', 'harga_pokok', 'hargapoko', 'harga_poko', 'hargabeli', 'harga_beli'],
        'HargaJual' => ['hargajual', 'harga_jual'],
        'Keterangan' => ['keterangan', 'keteranga', 'deskripsi'],
        'Poin' => ['poin', 'point'],
        'KomisiSales' => ['komisisales', 'komisi_sales', 'komisisale', 'komisi_sale'],
    ];

    /**
     * @var array<string, string>
     */
    private const FIELD_MAP = [
        'KodeItem' => 'KodeItem',
        'Barcode' => 'Barcode',
        'SKU' => 'SKU',
        'NamaItem' => 'NamaItem',
        'kategori_id' => 'kategori_id',
        'Merek' => 'Merek',
        'Stok' => 'Stok',
        'Rak' => 'Rak',
        'TipeItem' => 'TipeItem',
        'HargaBeli' => 'HargaBeli',
        'HargaJual' => 'HargaJual',
        'Keterangan' => 'Keterangan',
        'Foto' => 'Foto',
        'Status' => 'Status',
    ];

    /**
     * Build a non-mutating preview, including any validation errors.
     *
     * @return array<string, mixed>
     */
    public function preview(UploadedFile $file): array
    {
        $prepared = $this->prepare($file);
        $existing = $this->findRelevantBarang($prepared['rows']);

        return $this->summarize(
            $prepared['rows'],
            [...$prepared['errors'], ...$this->databaseErrors($prepared['rows'], $existing)],
            $existing,
            $prepared['has_stok_in_file']
        );
    }

    /**
     * Synchronize all valid rows atomically after explicit confirmation.
     *
     * @return array<string, mixed>
     */
    public function import(UploadedFile $file, string $previewHash): array
    {
        $prepared = $this->prepare($file);

        try {
            return DB::transaction(function () use ($prepared, $previewHash): array {
                $existing = $this->findRelevantBarang($prepared['rows'], true);
                $errors = [
                    ...$prepared['errors'],
                    ...$this->databaseErrors($prepared['rows'], $existing),
                ];
                $summary = $this->summarize(
                    $prepared['rows'],
                    $errors,
                    $existing,
                    $prepared['has_stok_in_file']
                );

                if ($errors === [] && ! hash_equals($summary['preview_hash'], $previewHash)) {
                    $summary['errors'][] = [
                        'row' => 1,
                        'field' => 'preview_hash',
                        'message' => 'Data barang berubah sejak pratinjau dibuat. Buat pratinjau baru sebelum mengonfirmasi.',
                    ];
                    $summary['valid'] = false;
                    $summary['requires_confirmation'] = false;
                    $summary['preview_hash'] = null;

                    return $summary;
                }

                if ($errors !== []) {
                    return $summary;
                }

                foreach ($prepared['rows'] as $row) {
                    $values = $row['values'];
                    $barang = $existing->firstWhere('KodeItem', $values['KodeItem']);

                    if ($barang === null) {
                        Barang::create($values);

                        continue;
                    }

                    $barang->fill($values);

                    if ($barang->isDirty()) {
                        $barang->save();
                    }
                }

                $summary['requires_confirmation'] = false;
                $summary['preview_hash'] = null;

                return $summary;
            }, 3);
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw $exception;
            }

            return [
                'valid' => false,
                'requires_confirmation' => false,
                'stock_will_be_set_from_file' => true,
                'blank_optional_cells_are_cleared' => true,
                'created' => 0,
                'updated' => 0,
                'unchanged' => 0,
                'stock_changes' => [],
                'errors' => [[
                    'row' => 0,
                    'field' => 'database',
                    'message' => 'Import dibatalkan karena terjadi konflik data atau kategori berubah saat import. Buat pratinjau baru.',
                ]],
                'preview_hash' => null,
            ];
        }
    }

    /**
     * @return array{rows: list<array{row: int, values: array<string, mixed>}>, errors: list<array{row: int, field: string, message: string}>, has_stok_in_file: bool}
     */
    private function prepare(UploadedFile $file): array
    {
        $sheets = Excel::toCollection(new HeadingRowImport, $file);

        if ($sheets->count() !== 1) {
            return [
                'rows' => [],
                'errors' => [[
                    'row' => 1,
                    'field' => 'file',
                    'message' => 'File import harus memiliki tepat satu sheet.',
                ]],
                'has_stok_in_file' => false,
            ];
        }

        $headingSheet = $sheets->first();
        $headingRow = $headingSheet?->first();
        $headings = $headingRow instanceof Collection
            ? $headingRow->values()->all()
            : [];
        $headings = array_map(fn ($h) => is_string($h) ? strtolower(trim($h)) : $h, $headings);
        $errors = [];

        $isLegacyFormat = in_array('kategori_id', $headings, true);

        if ($isLegacyFormat) {
            foreach (self::LEGACY_HEADERS as $header) {
                if (count(array_filter($headings, fn (mixed $value): bool => $value === $header)) !== 1) {
                    $errors[] = [
                        'row' => 1,
                        'field' => $header,
                        'message' => "Header {$header} wajib tersedia tepat satu kali.",
                    ];
                }
            }
        } else {
            $headingCounts = array_count_values(array_map('strval', $headings));
            foreach ($headingCounts as $head => $count) {
                if ($count > 1) {
                    $errors[] = [
                        'row' => 1,
                        'field' => (string) $head,
                        'message' => "Header '{$head}' terduplikasi ({$count} kali) di file Excel.",
                    ];
                }
            }

            foreach (self::NEW_FORMAT_COLUMNS as $colName => $aliases) {
                $matched = array_filter($headings, fn (mixed $value): bool => in_array($value, $aliases, true));
                if (count($matched) === 0) {
                    $errors[] = [
                        'row' => 1,
                        'field' => $colName,
                        'message' => "Header {$colName} wajib tersedia di file Excel.",
                    ];
                }
            }
        }

        if ($errors !== []) {
            return ['rows' => [], 'errors' => $errors, 'has_stok_in_file' => false];
        }

        $import = new BarangImport;
        Excel::import($import, $file);

        $hasStokInFile = in_array('stok', $headings, true) || in_array('stock', $headings, true);
        $hasStatusInFile = in_array('status', $headings, true);
        $hasFotoInFile = in_array('foto', $headings, true) || in_array('gambar', $headings, true);

        $categories = Kategori::all();

        $codesInFile = [];
        foreach ($import->rows as $sourceRow) {
            $source = $sourceRow->all();
            if ($this->isEmptyRow($source)) {
                continue;
            }
            $code = $this->extractField($source, ['kodeitem', 'kode_item']);
            if ($code !== null && $code !== '') {
                $codesInFile[] = (string) $code;
            }
        }
        $existingByCode = $codesInFile !== []
            ? Barang::whereIn('KodeItem', $codesInFile)->get()->keyBy('KodeItem')
            : collect();

        $preparedRows = [];
        $seenValues = [
            'KodeItem' => [],
            'Barcode' => [],
            'SKU' => [],
        ];

        foreach ($import->rows as $index => $sourceRow) {
            $rowNumber = (int) $index + 2;
            $source = $sourceRow->all();

            if ($this->isEmptyRow($source)) {
                continue;
            }

            $kodeItem = $this->extractField($source, ['kodeitem', 'kode_item']);
            $barcode = $this->extractField($source, ['barcode']);
            $sku = $this->extractField($source, ['sku']);
            $namaItem = $this->extractField($source, ['namaitem', 'nama_item']);
            $merek = $this->extractField($source, ['merek', 'merk']);
            $rak = $this->extractField($source, ['rak']);
            $tipeItem = $this->extractField($source, ['tipeitem', 'tipe_item']);
            $keterangan = $this->extractField($source, ['keterangan', 'keteranga', 'deskripsi']);

            $hargaBeliRaw = $this->extractField($source, ['hargapokok', 'harga_pokok', 'hargapoko', 'harga_poko', 'hargabeli', 'harga_beli']);
            $hargaBeli = $this->sanitizeNumeric($hargaBeliRaw);

            $hargaJualRaw = $this->extractField($source, ['hargajual', 'harga_jual']);
            $hargaJual = $this->sanitizeNumeric($hargaJualRaw);

            if ($isLegacyFormat) {
                $kategoriId = $this->extractField($source, ['kategori_id']);
                if (is_numeric($kategoriId) && (float) $kategoriId === floor((float) $kategoriId)) {
                    $kategoriId = (int) $kategoriId;
                }
            } else {
                $jenis = $this->extractField($source, ['jenis', 'kategori', 'nama_kategori']);
                $kategoriId = $this->resolveKategoriId($jenis, $categories);

                if ($jenis !== null && $kategoriId === null) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'field' => 'Jenis',
                        'message' => "Jenis '{$jenis}' tidak cocok dengan kategori apapun di database.",
                    ];
                }
            }

            $existingItem = $kodeItem ? $existingByCode->get($kodeItem) : null;

            if ($hasStokInFile) {
                $stokVal = $this->extractField($source, ['stok', 'stock']);
                $stok = is_numeric($stokVal) && (float) $stokVal === floor((float) $stokVal) ? (int) $stokVal : $stokVal;
            } else {
                $stok = $existingItem ? (int) $existingItem->Stok : 0;
            }

            if ($hasStatusInFile) {
                $statusVal = $this->extractField($source, ['status']);
                if (is_string($statusVal)) {
                    $statusVal = strtolower(str_replace([' ', '-'], '_', $statusVal));
                }
                $status = $statusVal;
            } else {
                $status = $existingItem ? $existingItem->Status : ((int) $stok === 0 ? 'tidak_tersedia' : 'tersedia');
            }

            if (is_numeric($stok) && (int) $stok === 0) {
                $status = 'tidak_tersedia';
            } elseif (is_numeric($stok) && (int) $stok > 0 && ($status === null || $status === '' || ! $hasStatusInFile)) {
                $status = 'tersedia';
            }

            if ($hasFotoInFile) {
                $foto = $this->extractField($source, ['foto', 'gambar']);
            } else {
                $foto = $existingItem ? $existingItem->Foto : null;
            }

            $values = [
                'KodeItem' => $kodeItem,
                'Barcode' => $barcode,
                'SKU' => $sku,
                'NamaItem' => $namaItem,
                'kategori_id' => $kategoriId,
                'Merek' => $merek,
                'Stok' => $stok,
                'Rak' => $rak,
                'TipeItem' => $tipeItem,
                'HargaBeli' => $hargaBeli,
                'HargaJual' => $hargaJual,
                'Keterangan' => $keterangan,
                'Foto' => $foto,
                'Status' => $status,
            ];

            $customMessages = $isLegacyFormat ? [] : [
                'kategori_id.required' => 'Kolom Jenis wajib diisi.',
                'kategori_id.exists' => 'Jenis tidak cocok dengan kategori apapun di database.',
                'HargaBeli.required' => 'Kolom HargaPokok wajib diisi.',
            ];

            $customAttributes = $isLegacyFormat ? [] : [
                'kategori_id' => 'Jenis',
                'HargaBeli' => 'HargaPokok',
            ];

            $validator = Validator::make($values, [
                'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
                'KodeItem' => ['required', 'string', 'max:50'],
                'Barcode' => ['required', 'string', 'max:50'],
                'SKU' => ['required', 'string', 'max:50'],
                'NamaItem' => ['required', 'string', 'max:100'],
                'Merek' => ['nullable', 'string', 'max:255'],
                'Stok' => ['required', 'integer', 'min:0', 'max:4294967295'],
                'Rak' => ['nullable', 'string', 'max:50'],
                'TipeItem' => ['nullable', 'string', 'max:100'],
                'HargaBeli' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
                'HargaJual' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
                'Keterangan' => ['nullable', 'string'],
                'Foto' => ['nullable', 'string', 'max:255'],
                'Status' => ['required', 'in:tersedia,tidak_tersedia'],
            ], $customMessages, $customAttributes);

            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'field' => $field,
                        'message' => $message,
                    ];
                }
            }

            foreach (['HargaBeli', 'HargaJual'] as $priceField) {
                if (is_numeric($values[$priceField] ?? null)) {
                    $values[$priceField] = number_format((float) $values[$priceField], 2, '.', '');
                }
            }

            foreach (['KodeItem', 'Barcode', 'SKU'] as $field) {
                $value = $values[$field] ?? null;

                if (! is_string($value) || $value === '') {
                    continue;
                }

                if (isset($seenValues[$field][$value])) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'field' => $field,
                        'message' => "Nilai {$field} duplikat dengan baris {$seenValues[$field][$value]}.",
                    ];

                    continue;
                }

                $seenValues[$field][$value] = $rowNumber;
            }

            $preparedRows[] = [
                'row' => $rowNumber,
                'values' => $values,
            ];
        }

        if ($preparedRows === [] && $errors === []) {
            $errors[] = [
                'row' => 1,
                'field' => 'file',
                'message' => 'File tidak berisi data barang.',
            ];
        }

        return ['rows' => $preparedRows, 'errors' => $errors, 'has_stok_in_file' => $hasStokInFile];
    }

    /**
     * @param  list<array{row: int, values: array<string, mixed>}>  $rows
     * @return Collection<int, Barang>
     */
    private function findRelevantBarang(array $rows, bool $lock = false): Collection
    {
        $values = collect($rows)->pluck('values');
        $codes = $values->pluck('KodeItem')->filter()->unique()->all();
        $barcodes = $values->pluck('Barcode')->filter()->unique()->all();
        $skus = $values->pluck('SKU')->filter()->unique()->all();

        if ($codes === [] && $barcodes === [] && $skus === []) {
            return collect();
        }

        $query = Barang::query()
            ->where(function ($query) use ($codes, $barcodes, $skus): void {
                if ($codes !== []) {
                    $query->whereIn('KodeItem', $codes);
                }

                if ($barcodes !== []) {
                    $method = $codes === [] ? 'whereIn' : 'orWhereIn';
                    $query->{$method}('Barcode', $barcodes);
                }

                if ($skus !== []) {
                    $method = $codes === [] && $barcodes === [] ? 'whereIn' : 'orWhereIn';
                    $query->{$method}('SKU', $skus);
                }
            })
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    /**
     * @param  list<array{row: int, values: array<string, mixed>}>  $rows
     * @param  Collection<int, Barang>  $existing
     * @return list<array{row: int, field: string, message: string}>
     */
    private function databaseErrors(array $rows, Collection $existing): array
    {
        $errors = [];
        $codesInFile = collect($rows)->pluck('values.KodeItem')->filter()->all();

        foreach ($rows as $row) {
            $values = $row['values'];

            foreach (['Barcode', 'SKU'] as $field) {
                $collision = $existing->first(
                    fn (Barang $barang): bool => $barang->{$field} === ($values[$field] ?? null)
                        && ! in_array($barang->KodeItem, $codesInFile, true)
                );

                if ($collision !== null) {
                    $errors[] = [
                        'row' => $row['row'],
                        'field' => $field,
                        'message' => "{$field} sudah digunakan oleh barang {$collision->KodeItem}.",
                    ];
                }
            }

        }

        return $errors;
    }

    /**
     * @param  list<array{row: int, values: array<string, mixed>}>  $rows
     * @param  list<array{row: int, field: string, message: string}>  $errors
     * @param  Collection<int, Barang>  $existing
     * @return array<string, mixed>
     */
    private function summarize(array $rows, array $errors, Collection $existing, bool $hasStokInFile = true): array
    {
        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $stockChanges = [];

        foreach ($rows as $row) {
            $values = $row['values'];
            $barang = $existing->firstWhere('KodeItem', $values['KodeItem']);

            if ($barang === null) {
                $created++;

                continue;
            }

            $changed = false;

            foreach ($values as $field => $value) {
                if (! $this->valuesMatch($barang->{$field}, $value, $field)) {
                    $changed = true;

                    if ($field === 'Stok' && $hasStokInFile) {
                        $stockChanges[] = [
                            'KodeItem' => $barang->KodeItem,
                            'NamaItem' => $barang->NamaItem,
                            'from' => (int) $barang->Stok,
                            'to' => (int) $value,
                        ];
                    }
                }
            }

            if ($changed) {
                $updated++;
            } else {
                $unchanged++;
            }
        }

        return [
            'valid' => $errors === [],
            'requires_confirmation' => $errors === [],
            'stock_will_be_set_from_file' => $hasStokInFile,
            'blank_optional_cells_are_cleared' => true,
            'created' => $created,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'stock_changes' => $stockChanges,
            'errors' => $errors,
            'preview_hash' => $errors === [] ? $this->confirmationHash($rows, $existing) : null,
        ];
    }

    /**
     * @param  list<array{row: int, values: array<string, mixed>}>  $rows
     * @param  Collection<int, Barang>  $existing
     */
    private function confirmationHash(array $rows, Collection $existing): string
    {
        $attributes = array_values(self::FIELD_MAP);
        $snapshot = $existing
            ->map(fn (Barang $barang): array => collect($barang->getAttributes())
                ->only($attributes)
                ->all())
            ->sortBy('KodeItem')
            ->values()
            ->all();
        $contents = json_encode([
            'rows' => $rows,
            'snapshot' => $snapshot,
        ], JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $contents, (string) config('app.key'));
    }

    private function valuesMatch(mixed $current, mixed $incoming, string $field): bool
    {
        if (in_array($field, ['HargaBeli', 'HargaJual'], true)) {
            return number_format((float) $current, 2, '.', '') === number_format((float) $incoming, 2, '.', '');
        }

        if ($field === 'Stok' || $field === 'kategori_id') {
            return (int) $current === (int) $incoming;
        }

        return $current === $incoming;
    }

    /**
     * @param  array<array-key, mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && (! is_string($value) || trim($value) !== '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve kategori_id by matching Jenis against existing Kategori records.
     *
     * @param  Collection<int, Kategori>  $categories
     */
    private function resolveKategoriId(mixed $jenis, Collection $categories): ?int
    {
        if ($jenis === null || $jenis === '') {
            return null;
        }

        if (is_numeric($jenis)) {
            $cat = $categories->firstWhere('id', (int) $jenis);
            if ($cat !== null) {
                return $cat->id;
            }
        }

        $jenisStr = trim((string) $jenis);

        $cat = $categories->first(fn (Kategori $k): bool => strcasecmp($k->nama_kategori, $jenisStr) === 0);
        if ($cat !== null) {
            return $cat->id;
        }

        $clean = fn (string $str): string => (string) preg_replace('/[^a-z0-9]/', '', strtolower($str));
        $normalizedJenis = $clean($jenisStr);

        $cat = $categories->first(fn (Kategori $k): bool => $clean($k->nama_kategori) === $normalizedJenis);
        if ($cat !== null) {
            return $cat->id;
        }

        $cat = $categories->first(function (Kategori $k) use ($clean, $normalizedJenis): bool {
            $catClean = $clean($k->nama_kategori);

            return $catClean !== '' && (str_contains($catClean, $normalizedJenis) || str_contains($normalizedJenis, $catClean));
        });
        if ($cat !== null) {
            return $cat->id;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  list<string>  $aliases
     */
    private function extractField(array $source, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $source)) {
                $val = $source[$alias];
                if (is_string($val)) {
                    $val = trim($val);
                }

                return $val === '' ? null : $val;
            }
        }

        return null;
    }

    private function sanitizeNumeric(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $cleaned = trim((string) preg_replace('/[^\d.,]/', '', $value));

            if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $cleaned)) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $cleaned)) {
                $cleaned = str_replace(',', '', $cleaned);
            } elseif (substr_count($cleaned, ',') === 1 && substr_count($cleaned, '.') === 0) {
                $cleaned = str_replace(',', '.', $cleaned);
            }

            if (is_numeric($cleaned)) {
                return $cleaned;
            }
        }

        return $value;
    }
}
