<?php

namespace Modules\Demo\Models;

use Modules\Core\Models\ModuleCategory;

class ExtendCore extends ModuleCategory
{
    public const OVERRIDE_TARGET = ModuleCategory::class;

    public function test(): string
    {
        return 'test';
    }
}
