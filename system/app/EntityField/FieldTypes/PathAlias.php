<?php

namespace App\EntityField\FieldTypes;

class PathAlias extends FieldTypeBase
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'path_alias';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '短网址';

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
    protected $valueModel = \App\EntityValue\EntityPathAlias::class;

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
        $meta = $meta ?? $this->getMeta();
        $current = $meta['value'] ?? null;
        $except = null;
        if ($this->field && $entity = $this->field->getBoundEntity()) {
            $except = '\''.$entity->getEntityPath().'\'';
        }

        return parent::getRules($meta)
            ->addRules('pathAlias')
            ->addRules('exists:path_alias.exists,'.$current.','.$except);
    }
}
