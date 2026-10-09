<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Berhasil.', int $status = 200)
    {
        if ($data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator) {
            $paginatedResponse = $data->response()->getData(true);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $paginatedResponse['data'],
                'links' => $paginatedResponse['links'],
                'meta' => $paginatedResponse['meta'],
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message = 'Terjadi Kesalahan.', int $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
