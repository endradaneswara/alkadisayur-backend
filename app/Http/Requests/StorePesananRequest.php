<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat_id' => ['nullable', 'integer', 'exists:alamat,id'],
            'lokasi_toko_id' => ['nullable', 'integer', 'exists:lokasi,id'],
            'metode' => ['required', 'in:pesan antar,ambil di toko'],
            'biaya_pengiriman' => ['nullable', 'numeric','min:0'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}