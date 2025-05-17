<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Factories\ModuleCategoryFactory;

/**
 * Module category model for categorizing modules in the ERP system.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ModuleCategory extends Model
{
    use HasFactory;

    protected $table = 'module_categories';
    protected $fillable = ['name', 'slug'];

    /**
     * Get a new factory instance for this model.
     */
    protected static function newFactory() : ModuleCategoryFactory {
        return new ModuleCategoryFactory();
    }

    /**
     * Test method.
     *
     * @return string
     */
    public function test(): string
    {
        return 'Origin';
    }
}
