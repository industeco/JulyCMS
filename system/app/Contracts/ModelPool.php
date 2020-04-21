<?php

namespace App\Contracts;

interface ModelPool
{
    public function getPrimaryKey();

    public static function fetch($id);

    public static function fetchMany($ids);
}
