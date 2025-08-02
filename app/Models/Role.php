<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'display_name_kh',
        'restricted',
    ];

    protected $casts = [
        'restricted' => 'boolean',
    ];

    /**
     * Get the permissions associated with this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Get the users that have this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if the role has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    /**
     * Check if the role has all of the given permissions.
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        $rolePermissionCount = $this->permissions()->whereIn('name', $permissionNames)->count();
        return $rolePermissionCount === count($permissionNames);
    }

    /**
     * Assign permissions to this role.
     */
    public function assignPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Remove permissions from this role.
     */
    public function removePermissions(array $permissionIds): void
    {
        $this->permissions()->detach($permissionIds);
    }
}
