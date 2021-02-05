<?php

namespace App\Entity\Exceptions;

use App\Concerns\ExceptionWrapper;

class EntityNotFoundException extends \RuntimeException
{
    use ExceptionWrapper;
}
