<?php

namespace Modules\Core\Services;

use Modules\Core\Models\TranslationAttribute;
use Modules\Core\Models\Language;

class TranslationImporter
{
    /**
     * Import specific translation file
     */
    public function importTranslationFile(string $moduleName, string $translationType): array
    {
        $filePath = PathService::getModulePath($moduleName) . "/Data/{$translationType}Translations.php";

        if (!file_exists($filePath)) {
            return ['status' => 'skipped', 'reason' => "No {$translationType}Translations.php found"];
        }

        $data = require $filePath;

        if (!is_array($data)) {
            return ['status' => 'error', 'reason' => 'Invalid translation file'];
        }

        $results = ['imported' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($data as $modelClass => $items) {
            foreach ($items as $identifier => $translations) {
                $result = $this->importItem($modelClass, $identifier, $translations);
                $results[$result]++;
            }
        }

        return ['status' => 'success', 'results' => $results];
    }

    /**
     * Import all translation files for module
     */
    public function importModuleTranslations(string $moduleName): array
    {
        $dataPath = PathService::getModulePath($moduleName) . "/Data/";
        $files = glob($dataPath . "*Translations.php");

        $totalResults = ['imported' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($files as $file) {
            $translationType = basename($file, 'Translations.php');
            $result = $this->importTranslationFile($moduleName, $translationType);

            if ($result['status'] === 'success') {
                foreach ($result['results'] as $key => $count) {
                    $totalResults[$key] += $count;
                }
            }
        }

        return ['status' => 'success', 'results' => $totalResults];
    }

    /**
     * Import single item with default language handling
     */
    protected function importItem(string $modelClass, string $identifier, array $translations): string
    {
        $model = $this->findModel($modelClass, $identifier);
        if (!$model) return 'skipped';

        $defaultLanguage = Language::getDefault();
        $defaultLocale = $defaultLanguage ? $defaultLanguage->code : config('app.locale', 'de');

        $hasChanges = false;

        foreach ($translations as $attribute => $localeValues) {
            foreach ($localeValues as $locale => $value) {

                // If this is the default language, update the model directly
                if ($locale === $defaultLocale) {
                    if ($model->{$attribute} !== $value) {
                        $model->{$attribute} = $value;
                        $model->save();
                        $hasChanges = true;
                    }
                } else {
                    // Other languages go to translation_attributes
                    $existing = TranslationAttribute::getTranslation($modelClass, $model->id, $attribute, $locale);
                    if ($existing !== $value) {
                        TranslationAttribute::setTranslation($modelClass, $model->id, $attribute, $locale, $value);
                        $hasChanges = true;
                    }
                }
            }
        }

        return $hasChanges ? 'updated' : 'skipped';
    }

    /**
     * Re-sync translations when default language changes
     */
    public function resyncAfterDefaultLanguageChange(string $oldLocale, string $newLocale): array
    {
        $results = ['models_updated' => 0, 'translations_moved' => 0];

        // Get all translation_attributes records
        $allTranslations = TranslationAttribute::select('model_type')
            ->distinct()
            ->get();

        foreach ($allTranslations as $translation) {
            $modelClass = $translation->model_type;

            if (!class_exists($modelClass)) continue;

            $models = $modelClass::all();

            foreach ($models as $model) {
                // Check if model uses TranslatableModel
                if (!method_exists($model, 'getTranslatableAttributes')) continue;

                $modelUpdated = false;
                $translatableAttributes = $model->getTranslatableAttributes();

                foreach ($translatableAttributes as $attribute) {
                    // Move old default language to translations
                    if ($model->{$attribute}) {
                        TranslationAttribute::setTranslation(
                            get_class($model),
                            $model->id,
                            $attribute,
                            $oldLocale,
                            $model->{$attribute}
                        );
                        $results['translations_moved']++;
                    }

                    // Move new default language from translations to model
                    $newDefaultValue = TranslationAttribute::getTranslation(
                        get_class($model),
                        $model->id,
                        $attribute,
                        $newLocale
                    );

                    if ($newDefaultValue) {
                        $model->{$attribute} = $newDefaultValue;
                        $modelUpdated = true;

                        // Remove from translations (it's now in the model)
                        TranslationAttribute::where('model_type', get_class($model))
                            ->where('model_id', $model->id)
                            ->where('attribute_name', $attribute)
                            ->where('locale', $newLocale)
                            ->delete();
                    }
                }

                if ($modelUpdated) {
                    $model->save();
                    $results['models_updated']++;
                }
            }
        }

        return $results;
    }

    /**
     * Find model by identifier
     */
    protected function findModel(string $modelClass, string $identifier): ?object
    {
        if (!class_exists($modelClass)) return null;

        $fields = ['code', 'slug', 'key', 'name', 'id'];
        foreach ($fields as $field) {
            try {
                $model = $modelClass::where($field, $identifier)->first();
                if ($model) return $model;
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * Delete all translations for a module
     */
    public function deleteModuleTranslations(string $moduleName): array
    {
        $results = ['deleted_translations' => 0, 'deleted_models' => []];

        // Correct pattern for the actual database format
        $pattern = "Modules\\\\{$moduleName}\\\\%";

        $moduleTranslations = TranslationAttribute::where('model_type', 'LIKE', $pattern)
            ->get()
            ->groupBy('model_type');

        foreach ($moduleTranslations as $modelType => $translations) {
            $deleted = TranslationAttribute::where('model_type', $modelType)->delete();

            $results['deleted_translations'] += $deleted;
            $results['deleted_models'][] = $modelType;
        }

        return $results;
    }

}
