<?php

namespace July\Core\EntityField\Exceptions;

class InvalidHostEntityException extends \RuntimeException
{
    protected $message = '字段的宿主实体不可用';
}
