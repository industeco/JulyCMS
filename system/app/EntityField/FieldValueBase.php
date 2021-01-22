<?php

namespace App\EntityField;

use App\Models\ModelBase;

abstract class FieldValueBase extends ModelBase
{
    abstract public function getValue();
    abstract public function setValue();
    abstract public function deleteValue();
    abstract public function searchValue();
}
