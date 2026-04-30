<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('plan') && $request->input('plan') !== 'All') {
            $query->where('plan', $request->input('plan'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $users = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection($users),
        ], 200);
    }

    public function show(int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully.',
            'data' => new UserResource($model),
        ], 200);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => $validated['role'] ?? 'user',
            'account_status' => $validated['account_status'] ?? 'active',
            'timezone' => $validated['timezone'] ?? null,
            'language' => $validated['language'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'plan' => $validated['plan'] ?? 'Free',
        ]);

        if (array_key_exists('assigned_projects', $validated)) {
            $user->assignedProjects()->sync($validated['assigned_projects'] ?? []);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(UserRequest $request, int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validated();

        $data = collect($validated)->only([
            'username',
            'email',
            'role',
            'first_name',
            'last_name',
            'phone',
            'account_status',
            'timezone',
            'language',
            'job_title',
            'plan',
        ])->all();

        if (array_key_exists('password', $validated) && $validated['password']) {
            $data['password'] = $validated['password'];
        }

        $model->update($data);

        if (array_key_exists('assigned_projects', $validated)) {
            $model->assignedProjects()->sync($validated['assigned_projects'] ?? []);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => new UserResource($model->fresh()),
        ], 200);
    }

    public function destroy(int $user): JsonResponse
    {
        $model = User::find($user);

        if (! $model) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        $model->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
            'data' => null,
        ], 200);
    }
}

