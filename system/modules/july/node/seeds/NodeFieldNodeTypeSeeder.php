<?php

namespace July\Node\Seeds;

use App\Utils\Arr;
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
    public function getNodeFieldNodeTypeTableRecords()
    {
        // 所有字段
        $allFields = [];

        // 全局字段
        $globalFields = [];

        foreach ((new NodeFieldSeeder)->getNodeFieldsTableRecords() as $field) {
            $allFields[$field['id']] = Arr::only($field, [
                'label',
                'description',
                'parameters',
            ]);
            if ($field['is_global'] ?? false) {
                $globalFields[] = $field['id'];
            }
        }

        // 类型关联字段
        $molds = [
            'basic' => ['content'],
            'list' => ['content'],
            'product' => ['summary', 'content', 'image_src', 'image_alt'],
            'article' => ['summary', 'content', 'image_src', 'image_alt'],
        ];

        // 生成记录
        $records = [];
        foreach ($molds as $mold_id => $moldFields) {
            foreach (array_merge($moldFields, $globalFields) as $index => $field_id) {
                $records[] = $allFields[$field_id] + [
                    'mold_id' => $mold_id,
                    'field_id' => $field_id,
                    'delta' => $index,
                ];
            }
        }

        return $records;
    }
}
