<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MigrateFlaskRooms extends Command
{
    protected $signature   = 'rooms:migrate-from-flask';
    protected $description = 'Migrate existing Flask JSON rooms to Supabase DB';

    public function handle(): int
    {
        $flaskUrl = config('app.flask_url', 'http://localhost:5000/api');

        $this->info("Fetching rooms from Flask: {$flaskUrl}/projects");

        try {
            $response   = Http::timeout(10)->get("{$flaskUrl}/projects");
            $flaskRooms = $response->json()['projects'] ?? [];
        } catch (\Exception $e) {
            $this->error("Flask not reachable: " . $e->getMessage());
            return 1;
        }

        $this->info("Found " . count($flaskRooms) . " rooms in Flask");

        $migrated = 0;
        $skipped  = 0;

        foreach ($flaskRooms as $roomData) {
            $externalId = $roomData['id'];
            $userId     = $roomData['user_id'] ?? null;

            if (!$userId) {
                $this->warn("Skipping room {$externalId} — no user_id");
                $skipped++;
                continue;
            }

            if (Room::where('external_id', $externalId)->exists()) {
                $this->line("Skipping {$externalId} — already exists");
                $skipped++;
                continue;
            }

            Room::create([
                'user_id'          => $userId,
                'external_id'      => $externalId,
                'name'             => $roomData['name'] ?? 'Room ' . substr($externalId, 0, 6),
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

            $this->info("Migrated: {$externalId} (user: {$userId})");
            $migrated++;
        }

        $this->newLine();
        $this->info("Done! Migrated: {$migrated}, Skipped: {$skipped}");
        $this->info("Total rooms in DB: " . Room::count());

        return 0;
    }
}
