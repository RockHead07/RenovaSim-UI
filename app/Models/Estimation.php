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
        if ($value) return $value;

        $jobTypeMap = config('renovasim.job_type_id', []);
        return $jobTypeMap[$this->job_type] ?? ucfirst($this->job_type ?? 'Estimasi');
    }
}
