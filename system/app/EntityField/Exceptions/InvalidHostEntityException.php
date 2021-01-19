<?php

namespace App\EntityField\Exceptions;

class InvalidBoundEntityException extends \InvalidArgumentException
{
    protected $message = '字段绑定实体无效';
}
