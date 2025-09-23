<?php

namespace Modules\Website\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Services\ContentManager;

class BaseRepository extends \Modules\Core\Abstracts\BaseRepository
{

    public function getFilteredData(array $params = []): Builder
    {
        $query = parent::getFilteredData($params);

        $websiteId = session()->get('website_id', null);

        $query->where(function ($q) use ($websiteId) {
            $q->where('website_id', $websiteId);
            $q->orWhereNull('website_id');
        });

        return $query;
    }

    protected function updateModel(Model $model, array $normalData): void
    {
        $model->fill($normalData);
        if($model->isDirty('content') && !$model->auto_public) {
            $model->is_changed = true;
        }
        $model->save();

        if ($model->auto_public) {
            ContentManager::publish($model);
        }
    }
}
