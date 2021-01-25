<?php

namespace July\Node\Seeds;

use Database\Seeds\SeederBase;

class NodeFieldNodeTypeSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['node_field_node_type'];

    /**
     * 获取 node_field_node_type 表数据
     *
     * @return array
     */
    protected function getNodeFieldNodeTypeTableRecords()
    {
        return [
            [
                'mold_id' => 'basic',
                'field_id' => 'title',
                'delta' => 0,
            ],
            [
                'mold_id' => 'basic',
                'field_id' => 'content',
                'delta' => 1,
            ],
            [
                'mold_id' => 'list',
                'field_id' => 'title',
                'delta' => 0,
            ],
            [
                'mold_id' => 'list',
                'field_id' => 'content',
                'delta' => 1,
            ],
            [
                'mold_id' => 'product',
                'field_id' => 'title',
                'delta' => 0,
            ],
            [
                'mold_id' => 'product',
                'field_id' => 'summary',
                'delta' => 1,
            ],
            [
                'mold_id' => 'product',
                'field_id' => 'content',
                'delta' => 2,
            ],
            [
                'mold_id' => 'product',
                'field_id' => 'image_src',
                'delta' => 3,
            ],
            [
                'mold_id' => 'product',
                'field_id' => 'image_alt',
                'delta' => 4,
            ],
            [
                'mold_id' => 'article',
                'field_id' => 'title',
                'delta' => 0,
            ],
            [
                'mold_id' => 'article',
                'field_id' => 'summary',
                'delta' => 1,
            ],
            [
                'mold_id' => 'article',
                'field_id' => 'content',
                'delta' => 2,
            ],
            [
                'mold_id' => 'article',
                'field_id' => 'image_src',
                'delta' => 3,
            ],
            [
                'mold_id' => 'article',
                'field_id' => 'image_alt',
                'delta' => 4,
            ],
        ];
    }
}
