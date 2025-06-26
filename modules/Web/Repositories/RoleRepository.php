<?php

namespace Modules\Web\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;

class RoleRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(new Role());
    }

    public function find(int $id): ?Model
    {
        return $this->model->with(['permissions'])->find($id);
    }

    public function permissionOptions(): array
    {
        return Permission::all()->map(function($item){
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'module' => $item->module,
            ];
        })->groupBy('module')->toArray();
    }
}
