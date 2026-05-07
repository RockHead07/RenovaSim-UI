<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricing_plan_id' => ['sometimes', 'exists:pricing_plans,id'],
            'feature' => ['sometimes', 'string', 'max:255'],
            'is_available' => ['boolean'],
        ];
    }
}
