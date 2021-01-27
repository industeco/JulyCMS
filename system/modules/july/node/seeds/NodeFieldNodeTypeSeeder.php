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
        $reserved = ['title'];
        $global = ['url', 'view', 'meta_title', 'meta_keywords', 'meta_description', 'meta_canonical'];
        $molds = [
            'basic' => ['content'],
            'list' => ['content'],
            'product' => ['summary', 'content', 'image_src', 'image_alt'],
            'article' => ['summary', 'content', 'image_src', 'image_alt'],
        ];

        $records = [];
        foreach ($molds as $id => $fields) {
            $fields = array_merge($reserved, $fields, $global);
            foreach ($fields as $index => $field) {
                $records[] = [
                    'mold_id' => $id,
                    'field_id' => $field,
                    'delta' => $index,
                ];
            }
        }
        return $records;
    }
}
