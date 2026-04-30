<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\PricingPlan;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof \App\Models\User ? $routeUser->id : $routeUser;

        $usernameRules = [
            'string',
            'max:255',
            Rule::unique('users', 'username')->ignore($userId),
        ];

        $emailRules = [
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($userId),
        ];

        if ($this->isMethod('post') || $this->isMethod('put')) {
            array_unshift($usernameRules, 'required');
            array_unshift($emailRules, 'required');
        } else {
            // PATCH: allow partial updates
            array_unshift($usernameRules, 'sometimes');
            array_unshift($emailRules, 'sometimes');
        }

        $base = [
            'username' => $usernameRules,
            'email' => $emailRules,
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['sometimes', 'in:user,admin,super_admin,owner'],
            'account_status' => ['sometimes', 'in:active,suspended,inactive'],
            'timezone' => ['nullable', 'timezone'],
            'language' => ['nullable', 'string', 'max:10'],
            'job_title' => ['nullable', 'string', 'max:255'],
            // Transition window:
            // - pricing_plan_id is the preferred identifier
            // - plan accepts either slug or legacy name
            'pricing_plan_id' => ['sometimes', 'integer', 'exists:pricing_plans,id'],
            'plan' => ['sometimes', 'string', 'max:50'],
            'assigned_projects' => ['sometimes', 'array'],
            'assigned_projects.*' => ['integer', 'exists:projects,id'],
        ];

        if ($this->isMethod('post')) {
            $base['password'] = ['required', 'string', 'min:6'];
        } else {
            $base['password'] = ['nullable', 'string', 'min:6'];
        }

        return $base;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $hasPlanId = $this->filled('pricing_plan_id');
            $hasPlanString = $this->filled('plan');

            if ($hasPlanId && $hasPlanString) {
                $validator->errors()->add('plan', 'Provide either plan or pricing_plan_id, not both.');
                $validator->errors()->add('pricing_plan_id', 'Provide either pricing_plan_id or plan, not both.');
                return;
            }

            if (! $hasPlanString) {
                return;
            }

            $plan = trim((string) $this->input('plan'));
            if ($plan === '') {
                return;
            }

            // Slug-first lookup. If not found, fallback to name.
            $bySlug = PricingPlan::query()->where('slug', $plan)->first();
            if ($bySlug) {
                return;
            }

            $byNameCount = PricingPlan::query()->where('name', $plan)->count();
            if ($byNameCount === 1) {
                return;
            }

            if ($byNameCount > 1) {
                $validator->errors()->add('plan', 'Plan name is ambiguous. Use plan slug or pricing_plan_id.');
                return;
            }

            $validator->errors()->add('plan', 'Plan not found. Use a valid plan slug or pricing_plan_id.');
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation error.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422)
        );
    }
}

