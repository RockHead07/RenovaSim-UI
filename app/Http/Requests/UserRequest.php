<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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

