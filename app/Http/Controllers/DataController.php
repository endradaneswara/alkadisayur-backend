<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Http\Requests\DataImportRequest;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;

class DataController extends Controller
{
    public function import(DataImportRequest $request)
    {
        Excel::import(
            new BarangImport,
            $request->file('file')
        );

        return response()->json([
            'message' => 'Data barang berhasil diimport.',
        ]);
    }

    public function export()
    {
        return Excel::download(
            new BarangExport,
            'data-barang.xlsx'
        );
    }
}

