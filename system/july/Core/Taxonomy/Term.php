<?php

namespace July\Core\Taxonomy;

use July\Core\Entity\EntityBase;

class Term extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'terms';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'vocabulary_id',
        'langcode',
        'updated_at',
    ];
}
