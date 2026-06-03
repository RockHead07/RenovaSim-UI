<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Show user's first room or create if doesn't exist
     */
    public function panel()
    {
        $user = Auth::user();
        $room = $user->rooms()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'My First Room',
                'description' => 'My First 3D Design',
                'width' => 4,
                'length' => 5,
                'height' => 3,
            ]
        );

        return redirect()->route('room.editor', $room);
    }

    /**
     * Show room editor
     */
    public function editor(Room $room)
    {
        // Ensure user owns the room
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $room->load('objects');

        return view('room.editor', compact('room'));
    }

    /**
     * Get room data for 3D editor (API)
     */
    public function show(Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $room->load('objects');

        return response()->json([
            'room' => [
                'id' => $room->id,
                'width' => (float) $room->width,
                'length' => (float) $room->length,
                'height' => (float) $room->height,
            ],
            'objects' => $room->objects->map(function ($obj) {
                return [
                    'id' => $obj->id,
                    'type' => $obj->type,
                    'position' => $obj->position,
                    'rotation' => $obj->rotation,
                    'scale' => $obj->scale,
                    'confidence' => $obj->confidence,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Save room data (API)
     */
    public function save(Request $request, Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:1',
            'length' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'objects' => 'nullable|array',
            'objects.*.id' => 'nullable|integer',
            'objects.*.type' => 'required|string',
            'objects.*.position' => 'required|array|size:3',
            'objects.*.position.*' => 'numeric',
            'objects.*.rotation' => 'required|array|size:3',
            'objects.*.rotation.*' => 'numeric',
            'objects.*.scale' => 'required|array|size:3',
            'objects.*.scale.*' => 'numeric',
            'objects.*.confidence' => 'nullable|numeric|min:0|max:1',
        ]);

        // Update room dimensions
        if (isset($validated['width'])) {
            $room->width = $validated['width'];
        }
        if (isset($validated['length'])) {
            $room->length = $validated['length'];
        }
        if (isset($validated['height'])) {
            $room->height = $validated['height'];
        }
        if (isset($validated['name'])) {
            $room->name = $validated['name'];
        }

        $room->save();

        // Delete all existing objects and create new ones
        $room->objects()->delete();

        if (isset($validated['objects'])) {
            foreach ($validated['objects'] as $objData) {
                RoomObject::create([
                    'room_id' => $room->id,
                    'type' => $objData['type'],
                    'position' => $objData['position'],
                    'rotation' => $objData['rotation'],
                    'scale' => $objData['scale'],
                    'confidence' => $objData['confidence'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Room saved successfully',
            'room' => [
                'id' => $room->id,
                'width' => (float) $room->width,
                'length' => (float) $room->length,
                'height' => (float) $room->height,
            ],
        ]);
    }

    /**
     * List user's rooms
     */
    public function index()
    {
        $rooms = Auth::user()->rooms()->get();

        return view('room.index', compact('rooms'));
    }

    /**
     * List all users' rooms for admin (Laravel DB, with Flask fallback)
     */
    public function adminIndex()
    {
        $rooms = \App\Models\Room::with('user:id,username,email,first_name,last_name')
            ->latest()
            ->get();

        if ($rooms->isEmpty()) {
            // Fallback to Flask JSON server for 3D rooms not yet in DB
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->get('http://localhost:5000/api/projects');

                if ($response->successful()) {
                    $flaskRooms = collect($response->json()['projects'] ?? []);
                    $userIds    = $flaskRooms->pluck('user_id')->filter()->unique();
                    $users      = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

                    $flaskRooms = $flaskRooms->map(fn($room) => array_merge($room, [
                        'username'  => $users[$room['user_id'] ?? '']?->username
                                       ?? ('User #' . ($room['user_id'] ?? '?')),
                        'full_name' => trim(
                                           ($users[$room['user_id'] ?? '']?->first_name ?? '') . ' ' .
                                           ($users[$room['user_id'] ?? '']?->last_name  ?? '')
                                       ) ?: null,
                        'email'     => $users[$room['user_id'] ?? '']?->email ?? '',
                    ]));

                    return view('admin.rooms.index', [
                        'rooms'      => collect([]),
                        'flaskRooms' => $flaskRooms,
                        'fromFlask'  => true,
                    ]);
                }
            } catch (\Exception $e) {
                // Flask server not available
            }
        }

        return view('admin.rooms.index', [
            'rooms'      => $rooms,
            'flaskRooms' => collect([]),
            'fromFlask'  => false,
        ]);
    }

    /**
     * Delete room (admin option for both DB and Flask saves)
     */
    public function adminDestroy(Request $request, $id)
    {
        $source = $request->input('source', 'db');

        if ($source === 'flask') {
            try {
                // Delete from Flask 3D server
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->delete("http://localhost:5000/api/rooms/{$id}");

                if ($response->successful()) {
                    return redirect()->route('admin.rooms.index')->with('success', 'Room deleted from 3D server successfully.');
                }

                $errorMsg = $response->json()['error'] ?? 'Unknown error';
                return redirect()->route('admin.rooms.index')->with('error', "Failed to delete room from 3D server: {$errorMsg}");
            } catch (\Exception $e) {
                return redirect()->route('admin.rooms.index')->with('error', 'Flask server is offline or unreachable. Cannot delete room.');
            }
        } else {
            // Delete from Database
            $room = Room::find($id);
            if (!$room) {
                return redirect()->route('admin.rooms.index')->with('error', 'Room not found in database.');
            }

            $room->delete(); // Cascades to room_objects in DB
            return redirect()->route('admin.rooms.index')->with('success', 'Room deleted from database successfully.');
        }
    }

    /**
     * Create new room
     */
    public function create()
    {
        return view('room.create');
    }

    /**
     * Store new room
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'width' => 'required|numeric|min:1',
            'length' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
        ]);

        $room = Auth::user()->rooms()->create($validated);

        return redirect()->route('room.editor', $room)->with('success', 'Room created successfully');
    }
}
