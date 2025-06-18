<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Abstracts\BaseModel;

class Role extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Relationships
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserExtended::class, 'user_roles')
            ->withPivot(['assigned_by', 'expires_at'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }


    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Permission Management
    public function givePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        if (!$this->hasPermission($permission)) {
            $this->permissions()->attach($permission);
        }

        return $this;
    }

    public function revokePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);
        return $this;
    }

    public function syncPermissions($permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('slug', $permission)->firstOrFail()->id;
            }
            return $permission instanceof Permission ? $permission->id : $permission;
        });

        $this->permissions()->syncWithoutDetaching($permissionIds);
        return $this;
    }

    public function hasPermission(Permission|string $permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions()->where('permissions.id', $permission->id)->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return collect($permissions)->some(fn($permission) => $this->hasPermission($permission));
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return collect($permissions)->every(fn($permission) => $this->hasPermission($permission));
    }

    // Helper Methods
    public function isSystemRole(): bool
    {
        return $this->is_system;
    }

    public function getPermissionsList(): array
    {
        return $this->permissions()->pluck('slug')->toArray();
    }
}
