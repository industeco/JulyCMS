<?php

namespace App\EntityField\FieldTypes;

class Image extends File
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'image';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '图片文件';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '可输入图片文件的路径，输入框右侧带文件浏览按钮';
}
