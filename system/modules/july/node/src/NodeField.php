<?php

namespace July\Node;

use App\EntityField\FieldBase;
use Illuminate\Support\Facades\Log;

class NodeField extends FieldBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_fields';

    /**
     * 获取实体类
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return Node::class;
    }

    /**
     * 获取翻译类
     *
     * @return string
     */
    public static function getTranslationClass()
    {
        return NodeFieldTranslation::class;
    }
}
