<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Material::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $materials = $query->orderBy('category')->orderBy('name')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Materials retrieved successfully.',
            'data'    => MaterialResource::collection($materials),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $material = Material::find($id);

        if (! $material) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Material not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Material retrieved successfully.',
            'data'    => new MaterialResource($material),
        ]);
    }

    public function store(MaterialRequest $request): JsonResponse
    {
        $material = Material::create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Material created successfully.',
            'data'    => new MaterialResource($material),
        ], 201);
    }

    public function update(MaterialRequest $request, int $id): JsonResponse
    {
        $material = Material::find($id);

        if (! $material) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Material not found.',
                'data'    => null,
            ], 404);
        }

        $material->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Material updated successfully.',
            'data'    => new MaterialResource($material->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $material = Material::find($id);

        if (! $material) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Material not found.',
                'data'    => null,
            ], 404);
        }

        $material->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Material deleted successfully.',
            'data'    => null,
        ]);
    }
}
