<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'logo'       => ['nullable', 'string', 'max:10'],
            'order'      => ['required', 'integer', 'min:1'],
            'logo_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:5120'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Validation error.',
                'data'    => ['errors' => $validator->errors()],
            ], 422)
        );
    }
}
