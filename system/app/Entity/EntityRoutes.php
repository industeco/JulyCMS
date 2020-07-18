<?php

namespace App\Entity;

use App\Contracts\RouteProviderInterface;
use App\Entity\User\UserEntityRoutes;

class EntityRoutes implements RouteProviderInterface
{
    public static function register()
    {
        UserEntityRoutes::register();
    }
}
