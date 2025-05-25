<?php

namespace Modules\Core\Models;

use Modules\Core\Abstracts\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'module',
        'resource',
        'action',
        'description',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    // Scopes
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    // Helper Methods
    public static function createForModule(string $module, array $permissions): void
    {
        foreach ($permissions as $permission) {
            static::firstOrCreate(
                ['slug' => $permission['slug']],
                array_merge($permission, ['module' => $module])
            );
        }
    }

    public function isSystemPermission(): bool
    {
        return $this->is_system;
    }

    public function getDisplayName(): string
    {
        return $this->name ?: ucwords(str_replace(['.', '_'], ' ', $this->slug));
    }
}
