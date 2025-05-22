<?php

namespace Modules\Core\Repositories;

use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Module;
use Modules\Core\Models\Override;

class OverrideRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(new Override);
    }

    public function check(Module $module): void
    {
        $overrides = $this->model->where('module_id', $module->id)->get();

        foreach ($overrides as $override) {
            $this->isOverrideValid($override);
        }
    }

    public function isOverrideValid(Override $override): void
    {
        if (!class_exists($override->original_class) || !class_exists($override->override_class)) {
            $override->delete();
        }

        try {
            $ref = new \ReflectionClass($override->override_class);

            if (!$ref->hasConstant('OVERRIDE_TARGET')) {
                $override->delete();
            }
        } catch (\ReflectionException $e) {
            return;
        }
    }
}
