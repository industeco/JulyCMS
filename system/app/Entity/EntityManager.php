<?php

namespace App\Entity;

use Illuminate\Support\Str;

class EntityManager
{
    public static function find(string $id)
    {
        if (class_exists($id)) {
            return $id;
        }

        $id = 'App\Entity\\'.Str::studly($id);
        if (class_exists($id)) {
            return $id;
        }

        return null;
    }
}
