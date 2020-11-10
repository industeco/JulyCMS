<?php

namespace July\Core\Node;

use July\Base\SeederProviderInterface;

class SeederProvider implements SeederProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSeeders()
    {
        return [
            seeds\NodeFieldSeeder::class,
            seeds\NodeTypeSeeder::class,
            seeds\CatalogSeeder::class,
        ];
    }
}
