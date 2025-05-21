<?php

namespace Modules\Core\Abstracts;


use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public static function model(): static
    {
        return app(static::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
