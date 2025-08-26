<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getCurrentTeamRoleNameAttribute(): ?string
    {
        $team = $this->currentTeam;
        if (!$team) return null;

        $pivotRole = $team->users()->where('user_id', $this->id)->first()?->pivot?->role;

        return $team->description ?: $pivotRole ?: null;
    }

    public function getCurrentTeamRoleKeyAttribute(): ?string
    {
        $team = $this->currentTeam;
        if (!$team) return null;

        return $team->users()->where('user_id', $this->id)->first()?->pivot?->role; 
    }

    public function getCurrentTeamRolePermissionsAttribute(): array
    {
        $team = $this->currentTeam;
        if (!$team) return [];

        return $team->permissions()->pluck('slug')->values()->all();
    }

    public function hasCurrentTeamPermission(string $slug): bool
    {
        $team = $this->currentTeam;
        return $team ? $team->userHasPermission($this, $slug) : false;
    }
}
