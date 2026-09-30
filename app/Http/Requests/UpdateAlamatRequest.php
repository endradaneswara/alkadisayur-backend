<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAlamatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'nama_alamat' => ['sometimes', 'required', 'string', 'max:100'],
            'nama_penerima' => ['sometimes', 'required', 'string', 'max:100'],
            'no_hp' => ['sometimes', 'required', 'string', 'max:20'],
            'alamat_lengkap' => ['sometimes', 'required', 'string'],
        ];
    }
}
