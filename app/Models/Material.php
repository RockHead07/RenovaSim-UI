<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price_per_unit',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'price_per_unit' => 'decimal:2',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_materials')
                    ->withPivot('quantity', 'subtotal')
                    ->withTimestamps();
    }
}