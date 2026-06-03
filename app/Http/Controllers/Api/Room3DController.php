<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Room3DController extends Controller
{
    private string $flaskUrl;

    public function __construct()
    {
        $this->flaskUrl = config('app.flask_url', 'http://localhost:5000/api');
    }

    private function roomQuery(string $id)
    {
        return Room::where('user_id', Auth::id())
            ->where(function ($q) use ($id) {
                $q->where('external_id', $id);
                if (is_numeric($id)) {
                    $q->orWhere('id', (int) $id);
                }
            });
    }

    public function status()
    {
        try {
            $r = Http::timeout(2)->get("{$this->flaskUrl}/status");

            return response()->json($r->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'offline', 'version' => '2.0.0']);
        }
    }

    public function furniture()
    {
        $r = Http::timeout(10)->get("{$this->flaskUrl}/furniture");

        return response()->json($r->json());
    }

    public function templates()
    {
        $r = Http::timeout(10)->get("{$this->flaskUrl}/templates");

        return response()->json($r->json());
    }

    public function paintColors()
    {
        $r = Http::timeout(10)->get("{$this->flaskUrl}/paint-colors");

        return response()->json($r->json());
    }

    public function projects()
    {
        $rooms = Room::where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($r) => $r->toFlaskFormat());

        return response()->json(['projects' => $rooms])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function getRoom(string $id)
    {
        $room = $this->roomQuery($id)->firstOrFail();

        return response()->json($room->toFlaskFormat());
    }

    public function saveRoom(Request $request, string $id)
    {
        $data = $request->all();

        $room = $this->roomQuery($id)->first();

        if (! $room) {
            $room = Room::create([
                'user_id'     => Auth::id(),
                'external_id' => $id,
                'name'        => $data['name'] ?? 'Room '.substr($id, 0, 6),
                'width'       => $data['width'] ?? 8,
                'length'      => $data['length'] ?? 10,
                'height'      => $data['height'] ?? 3.2,
            ]);
        }

        $room->update([
            'name'             => $data['name'] ?? $room->name,
            'width'            => $data['width'] ?? $room->width,
            'length'           => $data['length'] ?? $room->length,
            'height'           => $data['height'] ?? $room->height,
            'wall_color'       => $data['wall_color'] ?? $room->wall_color,
            'floor_color'      => $data['floor_color'] ?? $room->floor_color,
            'layout_data'      => $data['objects'] ?? $room->layout_data,
            'status'           => $data['status'] ?? 'saved',
            'applied_template' => $data['applied_template'] ?? $room->applied_template,
            'recommended_type' => $data['recommended_type'] ?? $room->recommended_type,
        ]);

        try {
            Http::timeout(10)->post(
                "{$this->flaskUrl}/rooms/{$id}/save",
                array_merge($data, ['user_id' => (string) Auth::id()])
            );
        } catch (\Exception $e) {
            // Flask sync optional
        }

        return response()->json($room->fresh()->toFlaskFormat());
    }

    public function saveThumbnail(Request $request, string $id)
    {
        $room = $this->roomQuery($id)->firstOrFail();

        $thumbnailData = $request->input('thumbnail');

        if ($thumbnailData && str_starts_with($thumbnailData, 'data:image')) {
            try {
                $base64    = preg_replace('/^data:image\/\w+;base64,/', '', $thumbnailData);
                $imageData = base64_decode($base64);
                $filename  = ($room->external_id ?? $room->id).'_'.time().'.jpg';

                Storage::disk('thumbnails')->put($filename, $imageData, 'public');
                $url = Storage::disk('thumbnails')->url($filename);

                $room->update(['thumbnail' => $url]);
            } catch (\Exception $e) {
                $room->update(['thumbnail' => $thumbnailData]);
            }
        } else {
            $room->update(['thumbnail' => $thumbnailData]);
        }

        return response()->json(['success' => true, 'thumbnail' => $room->fresh()->thumbnail]);
    }

    public function uploadImages(Request $request)
    {
        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        Log::info('uploadImages called', [
            'files_count' => count($files),
            'flask_url'   => $this->flaskUrl,
        ]);

        if (empty($files)) {
            return response()->json(['error' => 'No images provided'], 422);
        }

        try {
            $multipart = Http::timeout(120)->asMultipart();
            foreach ($files as $file) {
                $multipart = $multipart->attach(
                    'images',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }

            $response = $multipart->post("{$this->flaskUrl}/upload-images");

            if (! $response->successful()) {
                Log::error('Flask YOLO HTTP error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'error'  => 'YOLO service error',
                    'status' => $response->status(),
                    'body'   => $response->json() ?? $response->body(),
                ], $response->status() >= 400 ? $response->status() : 502);
            }

            $result = $response->json() ?? [];

            Log::info('Flask YOLO response', [
                'status'          => $response->status(),
                'has_room'        => ! empty($result['room']),
                'detected_assets' => $result['room']['detected_assets'] ?? [],
                'objects_count'   => count($result['room']['objects'] ?? []),
            ]);

        } catch (\Exception $e) {
            Log::error('uploadImages failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        if (! empty($result['room'])) {
            $roomData   = $result['room'];
            $externalId = $roomData['id'] ?? ($result['room_id'] ?? null);

            if ($externalId) {
                $room = Room::create([
                    'user_id'          => Auth::id(),
                    'external_id'      => $externalId,
                    'name'             => $roomData['name'] ?? 'Room '.substr($externalId, 0, 6),
                    'width'            => $roomData['width'] ?? 8,
                    'length'           => $roomData['length'] ?? 10,
                    'height'           => $roomData['height'] ?? 3.2,
                    'wall_color'       => $roomData['wall_color'] ?? '#f5f0eb',
                    'floor_color'      => $roomData['floor_color'] ?? '#c4a882',
                    'layout_data'      => $roomData['objects'] ?? [],
                    'status'           => 'generated',
                    'recommended_type' => $roomData['recommended_type'] ?? null,
                ]);

                $result['room']['supabase_id'] = $room->id;

                try {
                    Http::timeout(10)->post(
                        "{$this->flaskUrl}/rooms/{$externalId}/save",
                        array_merge($roomData, ['user_id' => (string) Auth::id()])
                    );
                } catch (\Exception $e) {
                    Log::warning('Flask sync failed after upload', ['error' => $e->getMessage()]);
                }
            }
        }

        $imageNames = $result['images'] ?? ($result['room']['images'] ?? []);
        if (! empty($imageNames)) {
            $uploadedUrls = [];
            foreach ($imageNames as $imageName) {
                $localPath = base_path("RAI/data/uploads/{$imageName}");
                if (file_exists($localPath)) {
                    try {
                        Storage::disk('room_uploads')->put($imageName, file_get_contents($localPath), 'public');
                        $uploadedUrls[] = Storage::disk('room_uploads')->url($imageName);
                    } catch (\Exception $e) {
                        $uploadedUrls[] = null;
                    }
                }
            }
            $result['image_urls'] = $uploadedUrls;
        }

        return response()->json($result);
    }

    public function applyTemplate(Request $request, string $id)
    {
        $response = Http::timeout(30)
            ->post("{$this->flaskUrl}/rooms/{$id}/apply-template", $request->all());

        $result = $response->json();

        if (! empty($result['room'])) {
            $room = $this->roomQuery($id)->first();

            if ($room) {
                $room->update([
                    'applied_template' => $request->input('template_id'),
                    'layout_data'      => $result['room']['objects'] ?? $room->layout_data,
                ]);
            }
        }

        return response()->json($result);
    }

    public function updateWall(Request $request, string $id)
    {
        $response = Http::timeout(10)
            ->post("{$this->flaskUrl}/rooms/{$id}/update-wall", $request->all());

        $room = $this->roomQuery($id)->first();

        if ($room && $request->filled('color')) {
            $room->update(['wall_color' => $request->input('color')]);
        }

        return response()->json($response->json());
    }

    public function deleteRoom(string $id)
    {
        $room = $this->roomQuery($id)->first();

        if ($room) {
            $room->delete();
        }

        try {
            Http::timeout(2)->delete("{$this->flaskUrl}/rooms/{$id}");
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json(['success' => true]);
    }

    public function renameRoom(Request $request, string $id)
    {
        $room = $this->roomQuery($id)->firstOrFail();

        $room->update(['name' => $request->input('name')]);

        try {
            Http::timeout(5)->post("{$this->flaskUrl}/rooms/{$id}/rename", $request->all());
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json(['success' => true, 'name' => $room->name]);
    }

    public function migrateFromFlask()
    {
        try {
            $response = Http::timeout(10)->get("{$this->flaskUrl}/projects");
            $flaskRooms = $response->json()['projects'] ?? [];
        } catch (\Exception $e) {
            return response()->json(['error' => 'Flask server not reachable'], 503);
        }

        $migrated = 0;
        foreach ($flaskRooms as $roomData) {
            $externalId = $roomData['id'] ?? null;
            $userId = $roomData['user_id'] ?? null;

            if (! $externalId || ! $userId) {
                continue;
            }

            if (Room::where('external_id', $externalId)->exists()) {
                continue;
            }

            Room::create([
                'user_id'          => (int) $userId,
                'external_id'      => $externalId,
                'name'             => $roomData['name'] ?? 'Room '.substr($externalId, 0, 6),
                'width'            => $roomData['width'] ?? 8,
                'length'           => $roomData['length'] ?? 10,
                'height'           => $roomData['height'] ?? 3.2,
                'wall_color'       => $roomData['wall_color'] ?? '#f5f0eb',
                'floor_color'      => $roomData['floor_color'] ?? '#c4a882',
                'layout_data'      => $roomData['objects'] ?? [],
                'status'           => $roomData['status'] ?? 'saved',
                'applied_template' => $roomData['applied_template'] ?? null,
                'recommended_type' => $roomData['recommended_type'] ?? null,
                'thumbnail'        => $roomData['thumbnail'] ?? null,
            ]);

            $migrated++;
        }

        return response()->json([
            'migrated' => $migrated,
            'total'    => count($flaskRooms),
        ]);
    }
}
