<?php

namespace July\Core\Entity\Exceptions;

use UnexpectedValueException;

class InvalidEntityException extends UnexpectedValueException
{
    protected $message = '无效的实体';
}
