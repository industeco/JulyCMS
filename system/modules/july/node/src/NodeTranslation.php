<?php

namespace July\Node;

use App\Entity\EntityTranslationBase;

class NodeTranslation extends EntityTranslationBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_translations';

    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return Node::class;
    }
}
