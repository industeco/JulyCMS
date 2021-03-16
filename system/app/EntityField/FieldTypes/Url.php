<?php

namespace App\EntityField\FieldTypes;

class Url extends FieldTypeBase
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'url';

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
    protected $description = '标准格式网址';

    /**
     * 指定创建或修改字段时可见的参数项
     *
     * @return array
     */
    public function getMetaKeys()
    {
        return ['default','options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(?array $meta = null)
    {
        return parent::getRules()->addRules('url');
    }
}
