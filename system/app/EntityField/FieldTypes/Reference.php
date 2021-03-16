<?php

namespace App\EntityField\FieldTypes;

use Illuminate\Support\Facades\Log;

class Reference extends FieldTypeBase
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'reference';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '实体引用';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '用于保存对其它实体的引用';

    /**
     * 字段值模型类
     *
     * @var string
     */
    protected $valueModel = \App\EntityValue\EntityReference::class;

    /**
     * 指定创建或修改字段时可见的参数项
     *
     * @return array
     */
    public function getMetaKeys()
    {
        return ['reference_scope'];
    }
}
