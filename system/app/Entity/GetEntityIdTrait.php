<?php

namespace App\Entity;

trait GetEntityIdTrait
{
    public function getEntityId(): string
    {
        return static::class;
    }
}
