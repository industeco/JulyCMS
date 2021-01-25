<?php

namespace App\Entity;

use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;
use App\Models\ModelBase;

abstract class EntityMoldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

    //
}
