<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superAdmin';
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'confirmed' => ['sometimes', 'boolean'],
            'preview_hash' => ['required_if:confirmed,1,true', 'nullable', 'string', 'size:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diunggah.',
            'file.file' => 'File yang diunggah tidak valid.',
            'file.mimes' => 'File harus berformat xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
