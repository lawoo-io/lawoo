<?php

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class RouteHelper extends Facade
{
    /**
     * Get the registered name of the component.
     * This key is used to bind the class in the service container.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'route.helper'; // This is the key under which the class is bound in the Service Container
    }

}
