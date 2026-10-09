<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Http\Requests\DataImportRequest;
use App\Services\DataImportService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataController extends Controller
{
    public function import(DataImportRequest $request, DataImportService $dataImportService): JsonResponse
    {
        $confirmed = $request->boolean('confirmed');
        $result = $confirmed
            ? $dataImportService->import($request->file('file'), $request->string('preview_hash')->toString())
            : $dataImportService->preview($request->file('file'));
        $valid = $result['valid'];

        return response()->json([
            'success' => $valid,
            'message' => $valid
                ? ($confirmed
                    ? 'Data barang berhasil disinkronkan.'
                    : 'Pratinjau import berhasil dibuat. Periksa perubahan stok lalu konfirmasi import.')
                : 'Import tidak dapat diproses karena terdapat kesalahan.',
            'data' => $result,
        ], $valid ? 200 : 422);
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(
            new BarangExport,
            'export-barang-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new BarangExport(true),
            'template-import-barang.xlsx'
        );
    }
}
