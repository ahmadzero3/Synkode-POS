<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id ?? null;

        return [
            'name'         => 'required|string|max:255',
            'code'         => 'required|integer|unique:registers,code' . ($id ? ',' . $id : ''),
            'user_id'      => 'required|exists:users,id',
            'phone_number' => 'nullable|string|max:20',
            'note'         => 'nullable|string',
            'active'       => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Register Name is required.',
            'code.required'    => 'Register Code is required.',
            'code.unique'      => 'Register Code already exists.',
            'user_id.required' => 'User is required.',
            'user_id.exists'   => 'Selected User is invalid.',
        ];
    }
}
