<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superAdmin';
    }

    public function rules(): array
    {
        $kategoriId = $this->route('kategori');

        return [
            'nama_kategori' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }
}
