<?php

namespace App\Services;

use App\Imports\BarangImport;
use App\Models\Barang;
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
    private const HEADERS = [
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
     * @var array<string, string>
     */
    private const FIELD_MAP = [
        'kodeitem' => 'KodeItem',
        'barcode' => 'Barcode',
        'sku' => 'SKU',
        'namaitem' => 'NamaItem',
        'kategori_id' => 'kategori_id',
        'merek' => 'Merek',
        'stok' => 'Stok',
        'rak' => 'Rak',
        'tipeitem' => 'TipeItem',
        'hargabeli' => 'HargaBeli',
        'hargajual' => 'HargaJual',
        'keterangan' => 'Keterangan',
        'foto' => 'Foto',
        'status' => 'Status',
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
            $existing
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
                $summary = $this->summarize($prepared['rows'], $errors, $existing);

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
     * @return array{rows: list<array{row: int, values: array<string, mixed>}>, errors: list<array{row: int, field: string, message: string}>}
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
            ];
        }

        $headingSheet = $sheets->first();
        $headingRow = $headingSheet?->first();
        $headings = $headingRow instanceof Collection
            ? $headingRow->values()->all()
            : (is_array($headingRow) ? array_values($headingRow) : []);
        $errors = [];

        foreach (self::HEADERS as $header) {
            if (count(array_filter($headings, fn (mixed $value): bool => $value === $header)) !== 1) {
                $errors[] = [
                    'row' => 1,
                    'field' => $header,
                    'message' => "Header {$header} wajib tersedia tepat satu kali.",
                ];
            }
        }

        if ($errors !== []) {
            return ['rows' => [], 'errors' => $errors];
        }

        $import = new BarangImport;
        Excel::import($import, $file);

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

            $values = [];

            foreach (self::FIELD_MAP as $header => $field) {
                $value = $source[$header] ?? null;
                $value = is_string($value) ? trim($value) : $value;
                $values[$field] = $value === '' ? null : $value;
            }

            if (is_numeric($values['Stok'] ?? null) && (float) $values['Stok'] === floor((float) $values['Stok'])) {
                $values['Stok'] = (int) $values['Stok'];
            }

            if (is_numeric($values['kategori_id'] ?? null) && (float) $values['kategori_id'] === floor((float) $values['kategori_id'])) {
                $values['kategori_id'] = (int) $values['kategori_id'];
            }

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
            ]);

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

        return ['rows' => $preparedRows, 'errors' => $errors];
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
    private function summarize(array $rows, array $errors, Collection $existing): array
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

                    if ($field === 'Stok') {
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
            'stock_will_be_set_from_file' => true,
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
}
