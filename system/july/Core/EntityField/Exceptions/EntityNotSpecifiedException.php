<?php

namespace July\Core\EntityField\Exceptions;

use UnexpectedValueException;

class EntityNotSpecifiedException extends UnexpectedValueException
{
    protected $message = '未指定可用的实体';
}
