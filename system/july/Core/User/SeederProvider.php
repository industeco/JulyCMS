<?php

namespace July\Core\User;

use July\Base\SeederProviderInterface;

class SeederProvider implements SeederProviderInterface
{
    public static function getSeeders()
    {
        return [
            seeds\UsersSeeder::class,
        ];
    }
}
