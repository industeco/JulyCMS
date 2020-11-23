<?php

namespace July\Core\Entity\Exceptions;

class InvalidBoundEntityException extends \RuntimeException
{
    protected $message = '绑定的实体无效';
}
