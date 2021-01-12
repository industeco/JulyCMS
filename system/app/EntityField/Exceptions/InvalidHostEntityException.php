<?php

namespace App\EntityField\Exceptions;

class InvalidHostEntityException extends \RuntimeException
{
    protected $message = '字段的宿主实体不可用';
}
