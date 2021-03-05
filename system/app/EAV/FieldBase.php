<?php

namespace App\EAV;

use App\Models\ModelBase;
use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;

abstract class FieldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;
}
