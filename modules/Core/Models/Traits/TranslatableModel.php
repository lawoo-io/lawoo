<?php

namespace Modules\Core\Models\Traits;

use Modules\Core\Models\TranslationAttribute;
use Modules\Core\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait TranslatableModel
{
    /**
     * Current translation context
     */
    protected ?string $translationLocale = null;

    /**
     * Translation fallback behavior
     */
    protected bool $noFallbackLocale = false;

    /**
     * Queued translations for new models
     */
    protected array $queuedTranslations = [];

    /**
     * Boot the trait
     */
    public static function bootTranslatableModel(): void
    {
        // Delete translations when model is deleted (nur einzeln)
        static::deleting(function (Model $model) {
            if ($model->exists) {
                TranslationAttribute::deleteModelTranslations(
                    get_class($model),
                    $model->getKey()
                );
            }
        });

        // Save queued translations after model is saved
        static::saved(function (Model $model) {
            /** @var self $model */
            if (isset($model->queuedTranslations) && !empty($model->queuedTranslations)) {
                foreach ($model->queuedTranslations as $locale => $attributes) {
                    foreach ($attributes as $key => $value) {
                        if ($value !== null) {
                            TranslationAttribute::setTranslation(
                                get_class($model),
                                $model->getKey(),
                                $key,
                                $locale,
                                $value
                            );
                        }
                    }
                }
                $model->queuedTranslations = [];
            }
        });
    }

    /**
     * Override newEloquentBuilder für Bulk-Delete-Handling
     */
    public function newEloquentBuilder($query): Builder
    {
        return new TranslatableQueryBuilder($query);
    }

    /**
     * Laravel 12 compatible: Override magic __get method
     */
    public function __get($key)
    {
        // Check if this field is translatable
        if ($this->isTranslatableAttribute($key)) {
            return $this->getTranslatedAttribute($key);
        }

        // Call Eloquent's default __get behavior
        return $this->getAttribute($key);
    }

    /**
     * Laravel 12 compatible: Override magic __set method
     */
    public function __set($key, $value): void
    {
        // Check if this field is translatable
        if ($this->isTranslatableAttribute($key)) {
            $this->setTranslatedAttribute($key, $value);
            return;
        }

        // Call Eloquent's default __set behavior
        $this->setAttribute($key, $value);
    }

    /**
     * Get translated attribute value
     */
    protected function getTranslatedAttribute(string $key): ?string
    {
        $locale = $this->getTranslationLocale();

        // If we're in default language, return original value
        if ($locale === $this->getDefaultLocale()) {
            return $this->attributes[$key] ?? null;
        }

        // Get translation if model exists
        if ($this->exists) {
            $translation = TranslationAttribute::getTranslation(
                get_class($this),
                $this->getKey(),
                $key,
                $locale
            );

            // If translation exists, return it
            if ($translation !== null) {
                return $translation;
            }
        }

        // Fallback to default language if allowed
        if (!$this->noFallbackLocale) {
            return $this->attributes[$key] ?? null;
        }

        return null;
    }

    /**
     * Set translated attribute value
     */
    protected function setTranslatedAttribute(string $key, ?string $value): void
    {
        $locale = $this->getTranslationLocale();

        // If we're in default language, set original value
        if ($locale === $this->getDefaultLocale()) {
            $this->attributes[$key] = $value;
            return;
        }

        // If model doesn't exist yet, queue for later
        if (!$this->exists) {
            $this->queuedTranslations[$locale][$key] = $value;
            return;
        }

        // Save translation
        if ($value !== null) {
            TranslationAttribute::setTranslation(
                get_class($this),
                $this->getKey(),
                $key,
                $locale,
                $value
            );
        }
    }

    /**
     * Set translation context
     */
    public function translateContext(string $locale): self
    {
        $this->translationLocale = $locale;
        return $this;
    }

    /**
     * Shorthand for translateContext
     */
    public function lang(string $locale): self
    {
        return $this->translateContext($locale);
    }

    /**
     * Disable fallback to default locale
     */
    public function noFallbackLocale(): self
    {
        $this->noFallbackLocale = true;
        return $this;
    }

    /**
     * Get specific translated attribute
     */
    public function getAttributeTranslated(string $attribute, string $locale): ?string
    {
        if (!$this->exists) {
            return null;
        }

        return TranslationAttribute::getTranslation(
            get_class($this),
            $this->getKey(),
            $attribute,
            $locale
        );
    }

    /**
     * Set specific translated attribute
     */
    public function setAttributeTranslated(string $attribute, string $value, string $locale): void
    {
        if ($this->exists) {
            TranslationAttribute::setTranslation(
                get_class($this),
                $this->getKey(),
                $attribute,
                $locale,
                $value
            );
        }
    }

    /**
     * Check if attribute is translatable
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    /**
     * Get translatable attributes
     */
    protected function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Get current translation locale
     */
    protected function getTranslationLocale(): string
    {
        return $this->translationLocale ?? app()->getLocale();
    }

    /**
     * Get default locale
     */
    protected function getDefaultLocale(): string
    {
        $defaultLanguage = Language::getDefault();
        return $defaultLanguage?->code ?? config('app.locale', 'de');
    }
}
