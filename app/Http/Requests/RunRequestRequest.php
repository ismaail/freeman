<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method'         => ['required', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'url'            => ['required', 'string', 'max:2048'],
            'request_id'     => ['nullable', 'integer'],
            'environment_id' => ['nullable', 'integer'],
            'headers'       => ['nullable', 'array'],
            'headers.*.key'     => ['nullable', 'string', 'max:255'],
            'headers.*.value'   => ['nullable', 'string', 'max:1000'],
            'headers.*.enabled' => ['nullable', 'boolean'],
            'body_type'     => ['nullable', Rule::in(['none', 'raw', 'form-data', 'x-www-form-urlencoded'])],
            'body'          => ['nullable', 'string'],
            'auth_type'     => ['nullable', Rule::in(['none', 'bearer', 'basic', 'api_key'])],
            'auth_data'     => ['nullable', 'array'],
        ];
    }
}
