<?php

namespace Specs\Exceptions;

class FieldTypeNotFoundException extends \RuntimeException
{
    protected $message = '字段类型未找到';
}
