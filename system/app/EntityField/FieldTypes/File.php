<?php

namespace App\EntityField\FieldTypes;

class File extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'file';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '文件名';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '可输入带路径文件名，输入框右侧带文件浏览按钮';
}
