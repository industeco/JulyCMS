<?php

namespace App\EntityField\Exceptions;

class FieldTypeNotFoundException extends \RuntimeException
{
    protected $message = '字段类型未找到';
}
