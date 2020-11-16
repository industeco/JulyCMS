<?php

namespace July\Core\Entity\Exceptions;

use App\Traits\ExceptionWrapper;

class EntityNotFoundException extends \RuntimeException
{
    use ExceptionWrapper;
}
