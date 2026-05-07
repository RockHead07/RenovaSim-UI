<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricing_plan_id' => ['required', 'exists:pricing_plans,id'],
            'feature' => ['required', 'string', 'max:255'],
            'is_available' => ['boolean'],
        ];
    }
}
