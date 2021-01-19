<?php

namespace App\Entity\Exceptions;

class InvalidEntityException extends \InvalidArgumentException
{
    protected $message = '无效的实体';
}
