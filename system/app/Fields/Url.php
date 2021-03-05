<?php

namespace App\Fields;

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
    protected $description = '标准格式网址';

    /**
     * {@inheritdoc}
     */
    public function getRules($value = null)
    {
        $rules = parent::getRules();
        $rules[] = "{type:'url', message:'网址格式不正确', trigger:'blur'}";

        return $rules;
    }
}
