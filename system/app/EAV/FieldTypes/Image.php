<?php

namespace App\Fields;

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
    protected $label = '图片';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '用于保存图片名（含路径），带文件浏览按钮';
}
