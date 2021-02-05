<?php

namespace App\Entity;

use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;

abstract class TranslatableEntityBase extends EntityBase implements TranslatableInterface
{
    use TranslatableTrait;
}
