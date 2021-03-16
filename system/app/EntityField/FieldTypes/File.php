<?php

namespace App\EntityField\FieldTypes;

class File extends FieldTypeBase
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'file';

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

    /**
     * 指定创建或修改字段时可见的参数项
     *
     * @return array
     */
    public function getMetaKeys()
    {
        return ['options'];
    }
}
