<?php

namespace Modules\Core\Abstracts;

use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


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
    public function find(int $id): ?Model
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

    public function update(int $id, array $data): Model
    {
        $model = $this->model->find($id);
        $model->fill($data);
        $model->save();
        return $model;
    }

    /**
     * @param array $params
     * @return Builder
     */
    public function getFilteredData(array $params = []): Builder
    {
        $query = $this->model->newQuery();

        if (!empty($params['search_filters_active'])) {
            $this->applySearchFilters($query, $params['search_filters_active']);
        }

        if (!empty($params['panel_filters_active']) && !empty($params['available_filters'])) {
            $this->applyPanelFilters($query, $params['panel_filters_active'], $params['available_filters']);
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

    protected function applySearchFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $key => $values) {
            $query->where(function ($subQuery) use ($key, $values) {
                foreach ($values as $value) {
                    $subQuery->orWhere($key, 'ILIKE', '%' . $value . '%');
                }
            });
        }
    }

    /**
     * Wendet Panel-Filter basierend auf der Konfiguration an.
     *
     * @param Builder $query
     * @param array $activeFilters Die aktiven Filter z.B. ['created_at_from' => '2025-06-11']
     * @param array $availableFilters Die komplette Konfiguration aus der ListView
     * @return void
     */
    protected function applyPanelFilters(Builder $query, array $activeFilters, array $availableFilters): void
    {
        // Flatten der Filter-Definitionen für einfachen Zugriff
        $flatDefinitions = [];
        foreach ($availableFilters as $group) {
            if (!empty($group['filters'])) {
                $flatDefinitions = array_merge($flatDefinitions, $group['filters']);
            }
        }

        foreach ($activeFilters as $key => $value) {
            if (!isset($flatDefinitions[$key])) continue;

            $definition = $flatDefinitions[$key];

            // Das zu filternde Datenbankfeld. Entweder explizit gesetzt oder der Key selbst.
            $field = $definition['field'] ?? $key;

            // Der Operator. Entweder aus der Definition oder der Standardwert '='.
            $operator = $definition['operator'] ?? '=';

            // Spezielle Logik für Operatoren, die besondere Werte erwarten
            switch ($operator) {
                case 'between':
                case 'date_between':
                    if (count($value) >= 2) {
                        $query->whereBetween($field, [$value['start'], $value['end']]);
                    }
                    break;

                case 'whereIn':
                case 'notWhereIn':
                    // Erwartet, dass $value bereits ein Array ist
                    $method = ($operator === 'whereIn') ? 'whereIn' : 'whereNotIn';
                    $query->{$method}($field, is_array($value) ? $value : [$value]);
                    break;

                case 'like':
                    $query->where($field, 'like', '%' . $value . '%');
                    break;

                default:
                    // Für alle Standard-Fälle wie =, !=, >, <, >=, <=
                    $query->where($field, $operator, $value);
                    break;
            }
        }
    }

//    protected function applyPanelFilters(Builder $query, array $filters, array $definitions): void
//    {
//        foreach ($filters as $key => $value) {
//            if (is_array($value)) {
//                $query->whereIn($key, $value);
//            } else {
//                $query->where($key, $value);
//            }
//        }
//    }

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
    public function delete(array $ids = [], bool $all = false, array $excludedIds = []): void
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
    public function authorize($permission)
    {
        if (!auth()->user()?->can($permission)) {
            throw new AuthorizationException();
        }
    }
}
