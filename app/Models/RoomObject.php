<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomObject extends Model
{
    use HasFactory;

    protected $table = 'room_objects';

    protected $fillable = [
        'room_id',
        'type',
        'position',
        'rotation',
        'scale',
        'confidence',
        'metadata',
    ];

    protected $casts = [
        'position' => 'array',
        'rotation' => 'array',
        'scale' => 'array',
        'metadata' => 'array',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
