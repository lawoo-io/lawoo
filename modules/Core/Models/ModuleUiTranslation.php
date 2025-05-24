<?php

namespace Modules\Core\Models;

use Modules\Core\Abstracts\BaseModel;

/**
 * Database model for module UI translations
 *
 * @property int $id
 * @property string $key_string
 * @property string $locale
 * @property string $module
 * @property string $translated_value
 * @property bool $is_auto_created
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ModuleUiTranslation extends BaseModel
{
    /**
     * The database table name.
     *
     * @var string
     */
    protected $table = 'module_ui_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key_string',
        'locale',
        'module',
        'translated_value',
        'is_auto_created',
        'removed'            // <- NEU HINZUGEFÜGT
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_auto_created' => 'boolean',
        'removed' => 'boolean',
    ];

    /**
     * Scope für auto-created Einträge
     */
    public function scopeAutoCreated($query)
    {
        return $query->where('is_auto_created', true);
    }

    /**
     * Scope für manuell erstellte Einträge
     */
    public function scopeManuallyCreated($query)
    {
        return $query->where('is_auto_created', false);
    }

    /**
     * Scope für bestimmtes Modul
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope für bestimmte Locale
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope für aktive (nicht entfernte) Einträge
     */
    public function scopeActive($query)
    {
        return $query->where('removed', false);
    }

    /**
     * Scope für entfernte Einträge
     */
    public function scopeRemoved($query)
    {
        return $query->where('removed', true);
    }
}
