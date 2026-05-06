<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PricingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'price'              => ['required', 'numeric', 'min:0'],
            'original_price'     => ['nullable', 'numeric', 'min:0'],
            'is_popular'         => ['sometimes', 'boolean'],
            'is_active'          => ['sometimes', 'boolean'],
            'features'           => ['nullable', 'array'],
            'features.*.feature' => ['nullable', 'string', 'max:255'],
            'features.*.is_available' => ['sometimes', 'boolean'],
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
