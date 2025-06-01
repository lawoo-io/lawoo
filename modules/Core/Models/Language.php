<?php

namespace Modules\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Abstracts\BaseModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Language active()
 * @method static \Illuminate\Database\Eloquent\Builder|Language inactive()
 * @method static \Illuminate\Database\Eloquent\Builder|Language default()
 * @method static \Illuminate\Database\Eloquent\Builder|Language nonDefault()
 * @method static \Illuminate\Database\Eloquent\Builder|Language ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|Language orderedByName()
 * @method static \Illuminate\Database\Eloquent\Builder|Language byCode(string $code)
 * @method static \Illuminate\Database\Eloquent\Builder|Language byCodes(array $codes)
 * @method static \Illuminate\Database\Eloquent\Builder|Language available()
 */
class Language extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    protected static $cacheKey = 'languages.active';
    protected static $defaultCacheKey = 'languages.default';

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Validierung und Cache-Management bei Änderungen
        static::saving(function ($language) {
            $language->validateDefaultLanguage();
        });

        static::saved(function ($language) {
            $language->clearCache();
        });

        static::deleted(function ($language) {
            $language->clearCache();
        });
    }

    /**
     * Scope: Nur aktive Sprachen
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope: Inaktive Sprachen
     */
    #[Scope]
    protected function inactive(Builder $query): void
    {
        $query->where('is_active', false);
    }

    /**
     * Scope: Standard-Sprache
     */
    #[Scope]
    protected function default(Builder $query): void
    {
        $query->where('is_default', true);
    }

    /**
     * Scope: Nicht-Standard Sprachen
     */
    #[Scope]
    protected function nonDefault(Builder $query): void
    {
        $query->where('is_default', false);
    }

    /**
     * Scope: Sortiert nach Code
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('code');
    }

    /**
     * Scope: Sortiert nach Name
     */
    #[Scope]
    protected function orderedByName(Builder $query): void
    {
        $query->orderBy('name');
    }

    /**
     * Scope: Nach Sprach-Code filtern
     */
    #[Scope]
    protected function byCode(Builder $query, string $code): void
    {
        $query->where('code', $code);
    }

    /**
     * Scope: Nach mehreren Codes filtern
     */
    #[Scope]
    protected function byCodes(Builder $query, array $codes): void
    {
        $query->whereIn('code', $codes);
    }

    /**
     * Scope: Verfügbar (aktiv oder Standard)
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('is_active', true)
                ->orWhere('is_default', true);
        });
    }

    /**
     * Alle aktiven Sprachen (gecacht)
     */
    public static function getActive(): \Illuminate\Support\Collection
    {
        return Cache::remember(static::$cacheKey, 3600, function () {
            return static::query()->active()->ordered()->get();
        });
    }

    /**
     * Standard-Sprache abrufen (gecacht)
     */
    public static function getDefault(): ?Language
    {
        return Cache::remember(static::$defaultCacheKey, 3600, function () {
            return static::query()->default()->first();
        });
    }

    /**
     * Sprache aktivieren
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Sprache deaktivieren (mit Validierung)
     */
    public function deactivate(): bool
    {
        if ($this->is_default) {
            throw new Exception('Cannot deactivate default language. Set another language as default first.');
        }

        $this->is_active = false;
        return $this->save();
    }

    /**
     * Als Standard-Sprache setzen
     */
    public function makeDefault(): bool
    {
        DB::transaction(function () {
            // Alte Standard-Sprache zurücksetzen
            static::where('is_default', true)->update(['is_default' => false]);

            // Diese Sprache als Standard setzen (und aktivieren)
            $this->is_default = true;
            $this->is_active = true;
            $this->save();
        });

        return true;
    }

    /**
     * Standard-Sprache entfernen (wenn andere Standard wird)
     */
    public function removeDefault(): bool
    {
        if (!$this->is_default) {
            return true; // Nichts zu tun
        }

        // Prüfen ob andere Standard-Sprache existiert
        $hasOtherDefault = static::where('id', '!=', $this->id)
            ->where('is_default', true)
            ->exists();

        if (!$hasOtherDefault) {
            throw new Exception('Cannot remove default flag. At least one language must be default.');
        }

        $this->is_default = false;
        return $this->save();
    }

    /**
     * Prüfen ob diese Sprache gelöscht werden kann
     */
    public function canBeDeleted(): bool
    {
        // Standard-Sprache kann nicht gelöscht werden
        if ($this->is_default) {
            return false;
        }

        // Hier später: Prüfung auf bestehende Übersetzungen
        // return !$this->hasTranslations();

        return true;
    }

    /**
     * Sprache sicher löschen
     */
    public function safeDelete(): bool
    {
        if (!$this->canBeDeleted()) {
            throw new Exception('Cannot delete this language. It is either default or has existing translations.');
        }

        return $this->delete();
    }

    /**
     * Validierung: Nur eine Standard-Sprache erlaubt
     */
    protected function validateDefaultLanguage(): void
    {
        if ($this->is_default && $this->isDirty('is_default')) {
            // Wenn diese Sprache Standard wird, andere zurücksetzen
            static::where('id', '!=', $this->id ?? 0)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Standard-Sprache muss aktiv sein
        if ($this->is_default) {
            $this->is_active = true;
        }
    }

    /**
     * Cache leeren
     */
    public function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
        Cache::forget(static::$defaultCacheKey);
    }

    /**
     * Alle Sprachen-Caches leeren
     */
    public static function clearAllCaches(): void
    {
        Cache::forget(static::$cacheKey);
        Cache::forget(static::$defaultCacheKey);
    }

    /**
     * Helper: Sprache nach Code finden
     */
    public static function findByCode(string $code): ?Language
    {
        return static::query()->byCode($code)->first();
    }

    /**
     * Helper: Aktive Sprache nach Code finden
     */
    public static function findActiveByCode(string $code): ?Language
    {
        return static::query()->active()->byCode($code)->first();
    }

    /**
     * Array aller aktiven Sprach-Codes
     */
    public static function getActiveCodes(): array
    {
        return static::getActive()->pluck('code')->toArray();
    }

    /**
     * Anzahl aktiver Sprachen
     */
    public static function getActiveCount(): int
    {
        return static::getActive()->count();
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }
}
