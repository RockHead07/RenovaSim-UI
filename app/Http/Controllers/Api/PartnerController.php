<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Partner::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $sortBy = $request->input('sort', 'order');
        $sortOrder = $request->input('order', 'asc');

        if (in_array($sortBy, ['name', 'order'], true)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('order');
        }

        $partners = $query->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Partners retrieved successfully.',
            'data'    => PartnerResource::collection($partners),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Partner not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Partner retrieved successfully.',
            'data'    => new PartnerResource($partner),
        ]);
    }

    public function store(PartnerRequest $request): JsonResponse
    {
        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo_image')) {
            $file     = $request->file('logo_image');
            $filename = 'partners/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $disk     = config('filesystems.default', 'public');
            Storage::disk($disk)->put($filename, file_get_contents($file), 'public');
            $data['logo_image'] = $filename;
        }

        $partner = Partner::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Partner created successfully.',
            'data'    => new PartnerResource($partner),
        ], 201);
    }

    public function update(PartnerRequest $request, int $id): JsonResponse
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Partner not found.',
                'data'    => null,
            ], 404);
        }

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo_image')) {
            if ($partner->logo_image) {
                try {
                    Storage::disk(config('filesystems.default', 'public'))->delete($partner->logo_image);
                } catch (\Exception $e) {}
            }
            $file     = $request->file('logo_image');
            $filename = 'partners/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $disk     = config('filesystems.default', 'public');
            Storage::disk($disk)->put($filename, file_get_contents($file), 'public');
            $data['logo_image'] = $filename;
        }

        $partner->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Partner updated successfully.',
            'data'    => new PartnerResource($partner->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Partner not found.',
                'data'    => null,
            ], 404);
        }

        if ($partner->logo_image) {
            try {
                Storage::disk(config('filesystems.default', 'public'))->delete($partner->logo_image);
            } catch (\Exception $e) {}
        }

        $partner->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Partner deleted successfully.',
            'data'    => null,
        ]);
    }
}
