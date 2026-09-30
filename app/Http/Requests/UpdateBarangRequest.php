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
            'kategori_id' => ['sometimes', 'required', 'integer', 'exists:kategori,id'],
            'KodeItem' => ['sometimes', 'required', 'string', 'max:50'],
            'Barcode' => ['sometimes', 'required', 'string', 'max:50'],
            'SKU' => ['sometimes', 'required', 'string', 'max:50'],
            'NamaItem' => ['sometimes', 'required', 'string', 'max:100'],
            'Merek' => ['nullable', 'string', 'max:100'],
            'Stok' => ['sometimes', 'required', 'integer', 'min:0'],
            'Rak' => ['nullable', 'string', 'max:50'],
            'TipeItem' => ['sometimes', 'nullable', 'string', 'max:100'],
            'HargaBeli' => ['sometimes', 'required', 'numeric', 'min:0'],
            'HargaJual' => ['sometimes', 'required', 'numeric', 'min:0'],
            'Keterangan' => ['nullable', 'string'],
            'Foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'Status' => ['sometimes', 'required', 'in:tersedia,tidak_tersedia'],
        ];
    }
}
