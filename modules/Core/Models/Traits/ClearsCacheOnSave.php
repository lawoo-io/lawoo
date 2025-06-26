<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsCacheOnSave
{
    /**
     * The "booting" method of the trait.
     *
     * We'll use the 'booted' method to register our model event listeners.
     * This is the modern way to do it in Laravel.
     */
    protected static function bootClearsCacheOnSave(): void
    {
        static::saved(fn ($model) => $model->clearModelCache());
        static::deleted(fn ($model) => $model->clearModelCache());
        static::updated(fn ($model) => $model->clearModelCache());
        static::created(fn ($model) => $model->clearModelCache());
    }

    /**
     * Clear the cache for the model.
     * It flushes the model's cache tag.
     */
    public function clearModelCache(): void
    {
        // Use tags to flush all related cache entries.
        Cache::tags($this->getCacheTag())->flush();
    }

    /**
     * Get the cache tag name for the model.
     * Defaulting to the model's table name.
     *
     * @return string
     */
    public function getCacheTag(): string
    {
        return 'table:' . $this->getTable();
    }
}
