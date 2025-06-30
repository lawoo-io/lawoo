<?php

namespace Modules\Core\Abstracts;


use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\ClearsCacheOnSave;

abstract class BaseModel extends Model
{

    use ClearsCacheOnSave;

    /**
     * The cache keys that should be automatically cleared.
     * This property should be overridden in child models.
     *
     * @var array<int, string>
     */
    protected array $cacheKeysToClear = [];

    public static function model(): static
    {
        return app(static::class);
    }

    /**
     * Scope: only active records
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope: Only inactive records
     */
    #[Scope]
    protected function archived(Builder $query): void
    {
        $query->where('is_active', false);
    }

}
