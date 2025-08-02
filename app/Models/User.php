<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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

    /**
     * Get the role assigned to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->hasPermission($permissionName);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->hasAnyPermission($permissionNames);
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->hasAllPermissions($permissionNames);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        $this->update(['role_id' => $role->id]);
    }

    /**
     * Remove the user's role.
     */
    public function removeRole(): void
    {
        $this->update(['role_id' => null]);
    }

    /**
     * Get all permissions for this user through their role.
     */
    public function getPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->role ? $this->role->permissions : collect();
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
