<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'room_type',
        'area_size',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'area_size'  => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}