<?php

namespace Modules\Core\Abstracts;

abstract class BaseService
{
    protected function log(string $message, array $context = [])
    {
        logger()->info('[Service] ' . $message, $context);
    }
}
