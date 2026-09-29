<?php 

namespace App\Traits; 

trait ApiResponse 
{ 
    protected function success(mixed $data = null, string $message = 'Berhasil.', int $status = 200) 
    { 
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