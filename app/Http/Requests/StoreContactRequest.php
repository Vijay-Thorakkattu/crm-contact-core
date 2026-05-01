<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', Rule::in(['account', 'lead'])],
            'data' => ['required', 'array'],
            'data.first_name' => ['required', 'string', 'max:255'],
            'data.last_name' => ['required', 'string', 'max:255'],
            'data.email' => [
                'nullable',
                'email',
                'max:255',
                'required_without:data.phone',
                Rule::unique('contacts', 'email'),
            ],
            'data.phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10,15}$/',
                'required_without:data.email',
                Rule::unique('contacts', 'phone'),
            ],
            'data.account_id' => ['nullable', 'integer', 'min:1'],
            'data.lead_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
