<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'building_type',
        'location',
        'room_type',
        'area_size',
        'total_cost',
        'estimations_count',
        'status',
    ];

    protected $casts = [
        'area_size'         => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'estimations_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estimations(): HasMany
    {
        return $this->hasMany(Estimation::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'project_materials')
                    ->withPivot('quantity', 'subtotal')
                    ->withTimestamps();
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Recalculate total_cost from all estimations and update estimations_count
     */
    public function recalculateTotals(): void
    {
        $this->estimations_count = $this->estimations()->count();
        $this->total_cost        = $this->estimations()->sum('cost_min'); // pakai cost_min (estimasi konservatif)
        $this->save();
    }
}