<?php

namespace App\Contracts;

interface PocketableInterface
{
    /**
     * 获取 Pocket ID
     *
     * @return string
     */
    public function getPocketId();
}
