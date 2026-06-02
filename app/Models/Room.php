<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description',
        'width', 'length', 'height',
        'layout_data', 'wall_color', 'floor_color',
        'external_id', 'status', 'applied_template',
        'recommended_type', 'thumbnail',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'width'       => 'float',
        'length'      => 'float',
        'height'      => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(RoomObject::class);
    }

    /**
     * Convert to Flask-compatible format
     */
    public function toFlaskFormat(): array
    {
        $objects = $this->layout_data ?? [];

        return [
            'id'               => $this->external_id ?? (string) $this->id,
            'name'             => $this->name,
            'width'            => $this->width,
            'length'           => $this->length,
            'height'           => $this->height,
            'wall_color'       => $this->wall_color ?? '#f5f0eb',
            'floor_color'      => $this->floor_color ?? '#c4a882',
            'objects'          => $objects,
            'object_count'     => is_array($objects) ? count($objects) : 0,
            'status'           => $this->status ?? 'saved',
            'applied_template' => $this->applied_template,
            'recommended_type' => $this->recommended_type,
            'thumbnail'        => $this->thumbnail,
            'user_id'          => (string) $this->user_id,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
