<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estimation extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'label',
        'mode',
        'job_type',
        'area',
        'location',
        'quality',
        'cost_min',
        'cost_max',
        'cost_display',
        'confidence_score',
        'confidence_label',
        'fastapi_response',
    ];

    protected $casts = [
        'area'             => 'decimal:2',
        'cost_min'         => 'decimal:2',
        'cost_max'         => 'decimal:2',
        'cost_display'     => 'string',
        'confidence_score' => 'decimal:2',
        'fastapi_response' => 'array',
    ];

    public function getFastapiResponseAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = $value;
        for ($i = 0; $i < 3 && is_string($decoded); $i++) {
            $next = json_decode($decoded, true);
            if ($next === null && json_last_error() !== JSON_ERROR_NONE) {
                break;
            }
            $decoded = $next;
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Auto-generate label from job_type if not set
     */
    public function getLabelAttribute($value): string
    {
        $jobTypeMap = config('renovasim.job_type_id', []);

        if ($value) {
            $key = strtolower($value);
            return $jobTypeMap[$key] ?? $value;
        }

        return $jobTypeMap[$this->job_type] ?? ucfirst($this->job_type ?? 'Estimasi');
    }
}
