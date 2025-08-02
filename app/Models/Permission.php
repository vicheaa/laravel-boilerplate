<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'display_name_kh',
        'action',
        'subject',
        'description',
        'description_kh',
        'parent_id',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the parent permission.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * Get the child permissions.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    /**
     * Get all descendant permissions recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestor permissions recursively.
     */
    public function ancestors(): BelongsTo
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Check if this permission is a child of another permission.
     */
    public function isChildOf(Permission $permission): bool
    {
        return $this->parent_id === $permission->id;
    }

    /**
     * Check if this permission is a descendant of another permission.
     */
    public function isDescendantOf(Permission $permission): bool
    {
        $current = $this;
        while ($current->parent) {
            if ($current->parent_id === $permission->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Get the full permission name (action:subject).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->action}:{$this->subject}";
    }
}
