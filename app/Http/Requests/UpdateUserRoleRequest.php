<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superAdmin';
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:customer,admin,superAdmin'],
        ];
    }
}
