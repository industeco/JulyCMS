<?php

namespace App\Entity\Exceptions;

class InvalidBoundEntityException extends \RuntimeException
{
    protected $message = '绑定的实体无效';
}
