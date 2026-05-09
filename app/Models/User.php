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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;

#[Fillable(['username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName
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
        'account_status',
        'timezone',
        'language',
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
            'last_active_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'owner']);
    }

    public function getFilamentName(): string
    {
        return $this->name
            ?? $this->username
            ?? $this->email
            ?? 'User';
    }
}
