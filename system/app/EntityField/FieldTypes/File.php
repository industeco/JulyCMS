<?php

namespace App\EntityField\FieldTypes;

class File extends FieldTypeBase
{
    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '文件';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '用于保存文件名（含路径），带文件浏览按钮';
}
