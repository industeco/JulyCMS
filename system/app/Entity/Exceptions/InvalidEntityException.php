<?php

namespace App\Entity\Exceptions;

class InvalidEntityException extends \RuntimeException
{
    protected $message = '无效的实体';
}
