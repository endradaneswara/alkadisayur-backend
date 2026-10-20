<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'superAdmin']);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,berhasil,gagal,expired'],
        ];
    }
}
