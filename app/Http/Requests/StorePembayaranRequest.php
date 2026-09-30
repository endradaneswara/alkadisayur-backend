<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pesanan_id' => ['required', 'integer', 'exists:pesanan,id'],
            'metode_pembayaran' => ['required', 'in:QRIS,Transfer,COD'],
        ];
    }
}