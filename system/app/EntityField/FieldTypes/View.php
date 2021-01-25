<?php

namespace App\EntityField\FieldTypes;

class View extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'view';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '视图（模板）';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '视图（模板）文件名';

    /**
     * 字段值模型类
     *
     * @var string
     */
    protected $valueModel = \App\EntityField\EntityView::class;
}
