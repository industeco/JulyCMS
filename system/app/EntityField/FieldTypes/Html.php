<?php

namespace App\EntityField\FieldTypes;

class Html extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'html';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = 'HTML';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于 HTML 文档';
}
