<?php

namespace App\Entity;

use App\Modules\Translation\TranslatableInterface;
use App\Modules\Translation\TranslatableTrait;
use App\Models\ModelBase;

abstract class EntityMoldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

    //
}
