<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with('user');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('room_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'All') {
            $query->where('status', strtolower($request->input('status')));
        }

        $perPage = (int) $request->input('per_page', 50);
        $perPage = max(1, min(200, $perPage));

        $projects = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'status'  => 'success',
            'message' => 'Projects retrieved successfully.',
            'data'    => ProjectResource::collection($projects),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $project = Project::with(['user', 'materials'])->find($id);

        if (! $project) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Project not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Project retrieved successfully.',
            'data'    => new ProjectResource($project),
        ]);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'user_id'   => auth()->id(),
            'name'      => $request->validated('name'),
            'room_type' => $request->validated('room_type'),
            'area_size' => $request->validated('area_size'),
            'status'    => 'draft',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Project created successfully.',
            'data'    => new ProjectResource($project->load('user')),
        ], 201);
    }

    public function update(ProjectRequest $request, int $id): JsonResponse
    {
        $project = Project::find($id);

        if (! $project) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Project not found.',
                'data'    => null,
            ], 404);
        }

        $project->update($request->only('name', 'room_type', 'area_size'));

        return response()->json([
            'status'  => 'success',
            'message' => 'Project updated successfully.',
            'data'    => new ProjectResource($project->fresh()->load('user')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $project = Project::find($id);

        if (! $project) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Project not found.',
                'data'    => null,
            ], 404);
        }

        $project->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Project deleted successfully.',
            'data'    => null,
        ]);
    }
}
