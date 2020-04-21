<?php

namespace App\FieldTypes;

class TagsField extends FieldTypeBase
{
    public static function columns(array $config)
    {
        $column = [
            'type' => 'string',
        ];
        return [$column];
    }
}
