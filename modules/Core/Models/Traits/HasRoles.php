<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;

trait HasRoles
{
    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_by', 'expires_at'])
            ->withTimestamps();
    }

    public function activeRoles(): BelongsToMany
    {
        return $this->roles()->wherePivot('expires_at', '>', now())
            ->orWherePivotNull('expires_at');
    }

    // Role Management
    public function assignRole(Role|string $role, int $assignedBy = null, $expiresAt = null): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_by' => $assignedBy,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $this;
    }

    public function removeRole(Role|string $role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);
        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::where('slug', $role)->firstOrFail()->id;
            }
            return $role instanceof Role ? $role->id : $role;
        });

        $this->roles()->sync($roleIds);
        return $this;
    }

    // Role Checks
    public function hasRole(Role|string $role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('slug', $role)->exists();
        }

        return $this->roles()->where('roles.id', $role->id)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->some(fn($role) => $this->hasRole($role));
    }

    public function hasAllRoles(array $roles): bool
    {
        return collect($roles)->every(fn($role) => $this->hasRole($role));
    }

    // Permission Checks (through roles)
    public function hasPermissionViaRole(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    public function getAllPermissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    public function getPermissionsList(): array
    {
        return $this->getAllPermissions()->pluck('slug')->toArray();
    }

    // Super Admin Check
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin ?? false;
    }

    public function canBypassPermissions(): bool
    {
        return $this->isSuperAdmin();
    }
}
