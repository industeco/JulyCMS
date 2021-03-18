<?php

namespace App\Entity;

use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;

abstract class TranslatableEntityBase extends EntityBase implements TranslatableInterface
{
    use TranslatableTrait;
}
