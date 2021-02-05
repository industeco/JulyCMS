<?php

namespace App\EntityField\FieldTypes;

use Illuminate\Support\Facades\Log;

class Text extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'text';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '多行文字';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于多行无格式内容';
}
