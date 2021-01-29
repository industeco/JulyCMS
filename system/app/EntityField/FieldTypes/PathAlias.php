<?php

namespace App\EntityField\FieldTypes;

class PathAlias extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'path_alias';

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
    protected $valueModel = \App\EntityField\EntityPathAlias::class;

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        $rules = parent::getRules();
        $rules[] = "{pattern:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/, message:'格式不正确', trigger:'blur'}";

        return $rules;
    }
}
