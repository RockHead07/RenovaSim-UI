<?php

namespace App\Models;

/**
 * @property string|null $account_status
 * @property \Illuminate\Support\Carbon|null $last_active_at
 */

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar_path',
        'password',
        'role',
        'is_admin',
        'google_id',
        'google_email',
        'account_status',
        'timezone',
        'language',
        'default_location',
        'job_title',
        'plan',
        'pricing_plan_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function estimations(): HasMany
    {
        return $this->hasMany(Estimation::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin || $this->email === 'admin@gmail.com';
    }
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function activePlan(): \App\Models\PricingPlan
    {
        if ($this->pricingPlan) {
            return $this->pricingPlan;
        }
        return \App\Models\PricingPlan::where('slug', 'free')->first()
            ?? new \App\Models\PricingPlan(['slug' => 'free']);
    }

    public function planLimit(string $featureKey): ?int
    {
        $plan    = $this->activePlan();
        $feature = $plan->features()->where('feature_key', $featureKey)->first();

        if (!$feature) return null;
        if ($feature->feature_value === 'unlimited') return null;
        if (!is_numeric($feature->feature_value)) return null;

        return (int) $feature->feature_value;
    }

    public function hasReachedLimit(string $featureKey, int $currentCount): bool
    {
        $limit = $this->planLimit($featureKey);
        if ($limit === null) return false;
        return $currentCount >= $limit;
    }
}
