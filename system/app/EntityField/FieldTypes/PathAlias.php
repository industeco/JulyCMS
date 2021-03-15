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
     * {@inheritdoc}
     */
    public function getRules(?array $meta = null)
    {
        $meta = $meta ?? $this->getMeta();

        return parent::getRules($meta)->addRules('pathAlias');
            // ->addRules('pathAlias|checkExists:path_alias.exists,'.($meta['value'] ?? null));

        // $rules[] = "{pattern:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/, message:'格式不正确', trigger:'blur'}";
        // $rules[] = "{validator:exists('{$exists}', '{$value}'), trigger:'blur'}";
    }
}
