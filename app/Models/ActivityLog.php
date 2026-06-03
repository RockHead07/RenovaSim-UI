<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'subject_name',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quick-log helper. Usage:
     *   ActivityLog::log($userId, 'create_project', 'Rumah Socrates');
     */
    public static function log(int $userId, string $event, string $subjectName = ''): void
    {
        $descriptionMap = [
            'create_project'     => 'membuat project estimasi',
            'delete_project'     => 'menghapus project estimasi',
            'create_estimation'  => 'melakukan estimasi pada project',
            'delete_estimation'  => 'menghapus estimasi',
            'create_room'        => 'memulai membuat 3D model design',
            'delete_room'        => 'menghapus 3D model design',
        ];

        $statusMap = [
            'create_project'     => 'Done',
            'delete_project'     => 'Deleted',
            'create_estimation'  => 'Done',
            'delete_estimation'  => 'Deleted',
            'create_room'        => 'Done',
            'delete_room'        => 'Deleted',
        ];

        $baseDesc = $descriptionMap[$event] ?? $event;
        $description = $subjectName
            ? "{$baseDesc} \"{$subjectName}\""
            : $baseDesc;

        self::create([
            'user_id'      => $userId,
            'event'        => $event,
            'description'  => $description,
            'subject_name' => $subjectName ?: null,
            'status'       => $statusMap[$event] ?? 'Done',
        ]);
    }
}
