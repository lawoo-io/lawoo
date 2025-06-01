<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Abstracts\BaseModel;

class TranslationAttribute extends BaseModel
{
    protected $fillable = [
        'locale',
        'model_type',
        'model_id',
        'attribute_name',
        'attribute_data'
    ];

    protected $casts = [
        'attribute_data' => 'array'
    ];

    /**
     * Get translation value
     */
    public function getValue(): ?string
    {
        return $this->attribute_data['value'] ?? null;
    }

    /**
     * Set translation value
     */
    public function setValue(string $value): void
    {
        $this->attribute_data = ['value' => $value];
    }

    /**
     * Find translation for model attribute
     */
    public static function getTranslation(string $modelType, int $modelId, string $attribute, string $locale): ?string
    {
        $cacheKey = "translation.{$modelType}.{$modelId}.{$attribute}.{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($modelType, $modelId, $attribute, $locale) {
            $translation = static::where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('attribute_name', $attribute)
                ->where('locale', $locale)
                ->first();

            return $translation?->getValue();
        });
    }

    /**
     * Set translation for model attribute
     */
    public static function setTranslation(string $modelType, int $modelId, string $attribute, string $locale, string $value): void
    {
        $translation = static::updateOrCreate([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'attribute_name' => $attribute,
            'locale' => $locale
        ], [
            'attribute_data' => ['value' => $value]
        ]);

        // Clear cache
        $cacheKey = "translation.{$modelType}.{$modelId}.{$attribute}.{$locale}";
        Cache::forget($cacheKey);
    }

    /**
     * Get all translations for a model
     */
    public static function getModelTranslations(string $modelType, int $modelId): array
    {
        return static::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->get()
            ->groupBy(['attribute_name', 'locale'])
            ->map(function ($locales) {
                return $locales->map(function ($translation) {
                    return $translation->first()->getValue();
                });
            })
            ->toArray();
    }

    /**
     * Delete all translations for a model
     */
    public static function deleteModelTranslations(string $modelType, int $modelId): void
    {
        static::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->delete();
    }
}
