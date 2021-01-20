<?php

namespace App\Entity;

use App\Modules\Translation\TranslatableInterface;
use App\Modules\Translation\TranslatableTrait;
use App\Model;

abstract class EntityMoldBase extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    //
}
