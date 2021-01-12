<?php

namespace App\Entity\Exceptions;

use App\Traits\ExceptionWrapper;

class EntityNotFoundException extends \RuntimeException
{
    use ExceptionWrapper;
}
