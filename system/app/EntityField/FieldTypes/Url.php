<?php

namespace App\EntityField\FieldTypes;

class Url extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'url';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '网址';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '实体路径别名';

    /**
     * 字段值模型类
     *
     * @var string
     */
    protected $valueModel = \App\EntityField\EntityPathAlias::class;
}
