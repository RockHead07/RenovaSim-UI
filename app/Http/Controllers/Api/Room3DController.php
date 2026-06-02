<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
            $r = Http::timeout(5)->get("{$this->flaskUrl}/status");

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

        return response()->json(['projects' => $rooms]);
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

        $room->update(['thumbnail' => $request->input('thumbnail')]);

        return response()->json(['success' => true]);
    }

    public function uploadImages(Request $request)
    {
        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        $http = Http::timeout(120);
        foreach ($files as $file) {
            $http = $http->attach(
                'images',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            );
        }

        $response = $http->post("{$this->flaskUrl}/upload-images");
        $result = $response->json();

        if (! empty($result['room'])) {
            $roomData = $result['room'];
            $externalId = $roomData['id'];

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
            Http::timeout(5)->delete("{$this->flaskUrl}/rooms/{$id}");
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
