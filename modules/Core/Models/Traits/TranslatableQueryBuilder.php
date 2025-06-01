<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\TranslationAttribute;

class TranslatableQueryBuilder extends Builder
{
    public function delete(): int
    {
        $model = $this->getModel();
        $modelClass = get_class($model);

        // Prüfen ob Model TranslatableModel Trait verwendet
        if (in_array(TranslatableModel::class, class_uses_recursive($modelClass))) {

            // IDs sammeln vor Delete
            $ids = $this->pluck($model->getKeyName());

            if ($ids->isNotEmpty()) {
                // Übersetzungen löschen
                TranslationAttribute::whereIn('model_id', $ids)
                    ->where('model_type', $modelClass)
                    ->delete();
            }
        }

        // Original Delete ausführen
        return parent::delete();
    }
}
