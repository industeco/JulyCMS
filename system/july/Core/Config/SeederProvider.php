<?php

namespace July\Core\Config;

use July\Base\SeederProviderInterface;

class SeederProvider implements SeederProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSeeders()
    {
        return [
            seeds\ConfigsSeeder::class,
        ];
    }
}
