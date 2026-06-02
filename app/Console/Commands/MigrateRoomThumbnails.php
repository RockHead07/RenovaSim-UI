<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateRoomThumbnails extends Command
{
    protected $signature   = 'rooms:migrate-thumbnails';

    protected $description = 'Upload existing room thumbnails to Supabase Storage';

    public function handle(): int
    {
        $dataDir = base_path('RAI/data');
        $rooms   = Room::whereNotNull('external_id')->get();

        $this->info("Found {$rooms->count()} rooms to process");

        foreach ($rooms as $room) {
            if ($room->thumbnail && str_starts_with($room->thumbnail, 'http')) {
                $this->line("Skipping {$room->external_id} — already has URL");
                continue;
            }

            if ($room->thumbnail && str_starts_with($room->thumbnail, 'data:image')) {
                $base64    = preg_replace('/^data:image\/\w+;base64,/', '', $room->thumbnail);
                $imageData = base64_decode($base64);
                $filename  = $room->external_id.'.jpg';

                try {
                    Storage::disk('thumbnails')->put($filename, $imageData, 'public');
                    $url = Storage::disk('thumbnails')->url($filename);
                    $room->update(['thumbnail' => $url]);
                    $this->info("Uploaded thumbnail: {$room->external_id}");
                } catch (\Exception $e) {
                    $this->error("Failed {$room->external_id}: ".$e->getMessage());
                }
                continue;
            }

            $localPath = $dataDir.'/uploads/'.$room->external_id.'_*.jpg';
            $files     = glob($localPath);

            if (! empty($files)) {
                $imageData = file_get_contents($files[0]);
                $filename  = $room->external_id.'.jpg';

                try {
                    Storage::disk('thumbnails')->put($filename, $imageData, 'public');
                    $url = Storage::disk('thumbnails')->url($filename);
                    $room->update(['thumbnail' => $url]);
                    $this->info("Uploaded from local: {$room->external_id}");
                } catch (\Exception $e) {
                    $this->error("Failed {$room->external_id}: ".$e->getMessage());
                }
            } else {
                $this->warn("No thumbnail found for: {$room->external_id}");
            }
        }

        $this->info('Done!');

        return 0;
    }
}
