<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePesananRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $method = $this->input('metode');

        if (is_string($method)) {
            $normalizedMethod = match (strtolower(trim($method))) {
                'pesan antar', 'pesan_antar' => 'pesan_antar',
                'ambil di toko', 'ambil_ditoko' => 'ambil_ditoko',
                default => $method,
            };

            $this->merge(['metode' => $normalizedMethod]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat_id' => ['nullable', 'integer', 'required_if:metode,pesan_antar', Rule::exists('alamat', 'id')->where('user_id', $this->user()?->id)],
            'lokasi_toko_id' => ['required', 'integer', Rule::exists('lokasi', 'id')->whereNull('deleted_at')],
            'metode' => ['required', 'in:pesan_antar,ambil_ditoko'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
