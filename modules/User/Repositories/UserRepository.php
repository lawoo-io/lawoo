<?php

namespace Modules\User\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\UserExtended;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new UserExtended());

    }

//     protected function loadRelationships(Builder $query): void
//     {
//        $query->with(['roles']);
//     }

    // Custom Filter Methods
    protected function filterRole(Builder $query, $value): void
    {
        $this->filterByRelation($query, 'roles', 'slug', $value);
    }

    protected function filterStatus(Builder $query, $value): void
    {
        if ($value === 'active') {
            $query->where('is_active', true);
        } elseif ($value === 'inactive') {
            $query->where('is_active', false);
        }
    }

    protected function filterDepartment(Builder $query, $value): void
    {
        $query->where('department_id', $value);
    }

    protected function filterCreatedAt(Builder $query, $value): void
    {
        if (is_array($value)) {
            $this->filterByDateRange($query, 'created_at', $value);
        }
    }

    // Custom Sorting Methods
    protected function sortRole(Builder $query, string $direction): void
    {
        $query->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
            ->orderBy('roles.name', $direction)
            ->select('users.*');
    }

    // User-spezifische Methoden
    public function getActiveUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getUsersByRole(string $roleSlug): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereHas('roles', function($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        })->get();
    }

    /**
     * @param array $ids
     * @param bool $all
     * @param array $excludedIds
     * @return void
     */
    public function delete(array $ids = [], bool $all = false, array $excludedIds = []): void
    {
        $this->authorize('user.users.delete');
        parent::delete($ids, $all, $excludedIds);
    }
}
