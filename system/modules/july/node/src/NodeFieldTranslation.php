<?php

namespace July\Node;

use App\EntityField\FieldTranslationBase;

class NodeFieldTranslation extends FieldTranslationBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'node_field_translations';

    /**
     * 获取绑定的字段类
     *
     * @return string
     */
    public function getFieldClass()
    {
        return NodeField::class;
    }
}
