<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\ClearsCacheOnSave;
use Modules\Core\Models\Traits\TranslatableModel;

class Navigation extends BaseModel
{

    use TranslatableModel, ClearsCacheOnSave;

    protected array $translatable = ['name', 'group'];
    public static string $translationIdentifier = 'key';

    protected $fillable = [
        'key',
        'route',
        'parent_id',
        'module',
        'level',
        'sort_order',
        'name',
        'middleware',
        'icon',
        'group_name',
        'group_order',
        'is_active',
        'is_user_modified',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'group_order' => 'integer',
        'is_active' => 'boolean',
        'is_user_modified' => 'boolean',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Navigation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Navigation::class, 'parent_id')
            ->orderBy('group_order')
            ->orderBy('sort_order');
    }

    // Scopes für Level-Base queries
    #[Scope]
    public function level(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    #[Scope]
    public function mainNavigation(Builder $query): Builder
    {
        return $query->where('level', 0);
    }

    #[Scope]
    public function byModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    #[Scope]
    public function ordered(Builder $query): Builder
    {
        return $query->orderBy('level')
            ->orderBy('group_order')
            ->orderBy('sort_order');
    }

    // Helper-methods
    public function isMainNavigation(): bool
    {
        return $this->level === 0;
    }

    public function hasIcon(): bool
    {
        return !empty($this->icon) && in_array($this->level, [0, 1]);
    }

    public function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    public function getDepth(): int
    {
        return $this->level;
    }

    // Hierarchy Methods
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function getDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    public function isChildOf(Navigation $navigation): bool
    {
        return $this->parent_id === $navigation->id;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function isNavigationActive(): bool
    {
        if (request()->routeIs($this->route) || request()->routeIs($this->route . '.*')) {
            return true;
        }

        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                if ($child->isNavigationActive()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getMainNavigation(): ?Navigation
    {
        if ($this->isMainNavigation()) {
            return $this;
        } elseif ($this->parent->isMainNavigation()) {
            return $this->parent;
        } elseif ($this->parent->parent->isMainNavigation()) {
            return $this->parent->parent;
        }
        return null;
    }

}
