<?php

namespace App\EntityField\FieldTypes;

use Illuminate\Support\Facades\Log;

class Input extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'input';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '单行文字';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于简短的无格式内容';
}
