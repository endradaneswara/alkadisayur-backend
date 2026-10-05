<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat_id' => ['nullable', 'integer', 'required_if:metode,pesan antar', Rule::exists('alamat', 'id')->where('user_id', $this->user()->id)],
            'lokasi_toko_id' => ['nullable', 'integer', 'required_if:metode,ambil di toko', 'exists:lokasi,id'],
            'metode' => ['required', 'in:pesan antar,ambil di toko'],
            'biaya_pengiriman' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
