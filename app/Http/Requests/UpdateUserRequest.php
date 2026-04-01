<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_super_admin;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId), 'regex:/^\S+$/'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'The username must not contain spaces.',
        ];
    }
}
