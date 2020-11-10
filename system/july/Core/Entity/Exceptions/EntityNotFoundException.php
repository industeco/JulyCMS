<?php

namespace July\Core\Entity\Exceptions;

use App\Traits\ExceptionWrapper;
use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    use ExceptionWrapper;
}
