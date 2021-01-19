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
                'node_type_id' => 'basic',
                'node_field_id' => 'title',
                'delta' => 0,
            ],
            [
                'node_type_id' => 'basic',
                'node_field_id' => 'content',
                'delta' => 1,
            ],
            [
                'node_type_id' => 'list',
                'node_field_id' => 'title',
                'delta' => 0,
            ],
            [
                'node_type_id' => 'list',
                'node_field_id' => 'content',
                'delta' => 1,
            ],
            [
                'node_type_id' => 'product',
                'node_field_id' => 'title',
                'delta' => 0,
            ],
            [
                'node_type_id' => 'product',
                'node_field_id' => 'summary',
                'delta' => 1,
            ],
            [
                'node_type_id' => 'product',
                'node_field_id' => 'content',
                'delta' => 2,
            ],
            [
                'node_type_id' => 'product',
                'node_field_id' => 'image_src',
                'delta' => 3,
            ],
            [
                'node_type_id' => 'product',
                'node_field_id' => 'image_alt',
                'delta' => 4,
            ],
            [
                'node_type_id' => 'article',
                'node_field_id' => 'title',
                'delta' => 0,
            ],
            [
                'node_type_id' => 'article',
                'node_field_id' => 'summary',
                'delta' => 1,
            ],
            [
                'node_type_id' => 'article',
                'node_field_id' => 'content',
                'delta' => 2,
            ],
            [
                'node_type_id' => 'article',
                'node_field_id' => 'image_src',
                'delta' => 3,
            ],
            [
                'node_type_id' => 'article',
                'node_field_id' => 'image_alt',
                'delta' => 4,
            ],
        ];
    }
}
