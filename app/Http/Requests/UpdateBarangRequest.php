<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'KodeItem' => ['required', 'string', 'max:50'],
            'Barcode' => ['required', 'string', 'max:50'],
            'SKU' => ['required', 'string', 'max:50'],
            'NamaItem' => ['required', 'string', 'max:100'],
            'Merek' => ['nullable', 'string', 'max:100'],
            'Stok' => ['required', 'integer', 'min:0'],
            'Rak' => ['nullable', 'string', 'max:50'],
            'TipeItem' => ['nullable', 'string', 'max:100'],
            'HargaBeli' => ['required', 'numeric', 'min:0'],
            'HargaJual' => ['required', 'numeric', 'min:0'],
            'Keterangan' => ['nullable', 'string'],
            'Foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'Status' => ['required', 'in:tersedia,tidak_tersedia'],
        ];
    }
}
