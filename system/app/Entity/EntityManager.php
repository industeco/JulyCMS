<?php

namespace App\Entity;

class EntityManager
{
    public static function findEntity(string $id)
    {
        if (class_exists($id)) {
            return $id;
        }
        return null;
    }
}
