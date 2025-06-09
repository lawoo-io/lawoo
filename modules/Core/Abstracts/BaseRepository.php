<?php

namespace Modules\Core\Abstracts;

use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


abstract class BaseRepository
{
    protected Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param $id
     * @return Model|null
     */
    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @param array $params
     * @return Builder
     */
    public function getFilteredData(array $params = []): Builder
    {
        $query = $this->model->newQuery();

        // Der alte 'Search'-Block wurde entfernt.
        // Alle Filter werden jetzt von der Methode applyFilters gehandhabt.
        if (!empty($params['filters'])) {
            $this->applyFilters(
                $query,
                $params['filters'],
                $params['search_fields'] ?? [] // Übergibt die erlaubten Text-Suchfelder
            );
        }

        // Sorting
        if (!empty($params['sort'])) {
            $this->applySorting($query, $params['sort']);
        } else {
            $this->applyDefaultSorting($query);
        }

        // Relationships
        $this->loadRelationships($query);

        // Select
        if (!empty($params['select'])) {
            $query->select($params['select']);
        }

        return $query;
    }

    protected function applyFilters(Builder $query, array $filters, array $textSearchFields): void
    {
        foreach ($filters as $key => $value) {

            if (empty($value)) continue;

            if (array_key_exists($key, $textSearchFields)) {

                if (is_array($value)) {
                    $query->where(function ($subQuery) use ($key, $value) {
                        foreach ($value as $singleValue) {
                            $subQuery->orWhereRaw('LOWER("' . $key . '") LIKE ?', ['%' . strtolower($singleValue) . '%']);
                        }
                    });
                } else {
                    $query->whereRaw('LOWER("' . $key . '") LIKE ?', ['%' . strtolower($value) . '%']);
                }
                continue;
            }

            // 2. Priorität: Prüfen, ob eine eigene Filter-Methode im Kind-Repository existiert.
            $customMethod = 'filter' . ucfirst($key);
            if (method_exists($this, $customMethod)) {
                $this->$customMethod($query, $value);
                continue;
            }

            // 3. Fallback: Standard-Filterung für exakte Treffer.
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }
    }

    /**
     * @param Builder $query
     * @param array $sort
     * @return void
     */
    protected function applySorting(Builder $query, array $sort): void
    {
        [$sortBy, $sortDirection] = $sort;

        // Custom sorting method in child repository
        $customMethod = 'sort' . ucfirst($sortBy);
        if (method_exists($this, $customMethod)) {
            $this->$customMethod($query, $sortDirection);
            return;
        }

        // Standard sorting
        if (str_contains($sortBy, '.')) {
            // Relationship sorting (z.B. 'roles.name')
            [$relation, $relationField] = explode('.', $sortBy, 2);
            $this->sortByRelation($query, $relation, $relationField, $sortDirection);
        } else {
            // Direct field sorting
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * @param Builder $query
     * @param string $relation
     * @param string $field
     * @param string $direction
     * @return void
     */
    protected function sortByRelation(Builder $query, string $relation, string $field, string $direction): void
    {
        // Standard JOIN für Relationship-Sorting
        $relatedTable = $this->model->{$relation}()->getRelated()->getTable();
        $foreignKey = $this->model->{$relation}()->getForeignKeyName();
        $localKey = $this->model->{$relation}()->getLocalKeyName();

        $query->leftJoin($relatedTable, "{$this->model->getTable()}.{$localKey}", '=', "{$relatedTable}.{$foreignKey}")
            ->orderBy("{$relatedTable}.{$field}", $direction)
            ->select("{$this->model->getTable()}.*");
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function applyDefaultSorting(Builder $query): void
    {
        $query->orderBy('created_at', 'desc');
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function loadRelationships(Builder $query): void
    {
        // Override in child repositories
        // z.B. $query->with(['roles', 'department']);
    }

    /**
     * Helper Methods für Child Repositories
     * @param Builder $query
     * @param string $relation
     * @param string $field
     * @param $value
     * @return void
     */
    protected function filterByRelation(Builder $query, string $relation, string $field, $value): void
    {
        $query->whereHas($relation, function($q) use ($field, $value) {
            if (is_array($value)) {
                $q->whereIn($field, $value);
            } else {
                $q->where($field, $value);
            }
        });
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param array $dateRange
     * @return void
     */
    protected function filterByDateRange(Builder $query, string $field, array $dateRange): void
    {
        if (!empty($dateRange['from'])) {
            $query->whereDate($field, '>=', $dateRange['from']);
        }
        if (!empty($dateRange['to'])) {
            $query->whereDate($field, '<=', $dateRange['to']);
        }
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param $value
     * @return void
     */
    protected function filterByBoolean(Builder $query, string $field, $value): void
    {
        if ($value === 'true' || $value === '1' || $value === 1) {
            $query->where($field, true);
        } elseif ($value === 'false' || $value === '0' || $value === 0) {
            $query->where($field, false);
        }
    }

    /**
     * @param array $ids
     * @param bool $all
     * @param array $excludedIds
     * @return void
     */
    protected function delete(array $ids = [], bool $all = false, array $excludedIds = []): void
    {
        $count = 0;
        try {
            if ($all) {
                $count = $this->model->whereNotIn('id', $excludedIds)->count();
                $this->model->whereNotIn('id', $excludedIds)->delete();
            } else {
                $count = $this->model->whereIn('id', $ids)->whereNotIn('id', $excludedIds)->count();
                $this->model->whereIn('id', $ids)->whereNotIn('id', $excludedIds)->delete();
            }
        } catch (\Exception $e) {
            Flux::toast($e->getMessage(), 'error');
        };

        Flux::toast(text: __('core::messages.records_deleted', ['count' => $count]), variant: $count ? 'success' : 'warning' );
    }

    /**
     * @param $permission
     * @return void
     */
    protected function authorize($permission)
    {
        if (!auth()->user()?->can($permission)) {
            throw new AuthorizationException();
        }
    }
}
