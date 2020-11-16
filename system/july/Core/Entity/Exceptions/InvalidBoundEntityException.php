<?php

namespace July\Core\Entity\Exceptions;

class InvalidBoundEntityException extends \RuntimeException
{
    protected $message = '无效的绑定实体';
}
