<?php

namespace July\Base;

interface SeederProviderInterface
{
    /**
     * 返回 seeder 数组
     *
     * @return array
     */
    public static function getSeeders();
}
