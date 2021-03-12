<?php

namespace App\EntityField\FieldTypes;

class PathAlias extends FieldTypeBase
{
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
     * {@inheritdoc}
     */
    public function getRules(array $meta)
    {
        $value = $meta['value'] ?? null;
        $meta['rules'] = ($meta['rules'] ?? '').'|pathAlias|checkExists:path_alias.exists,'.$value;

        return parent::getRules($meta);

        // $rules[] = "{pattern:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/, message:'格式不正确', trigger:'blur'}";

        // $rules[] = "{validator:exists('{$exists}', '{$value}'), trigger:'blur'}";
    }
}
