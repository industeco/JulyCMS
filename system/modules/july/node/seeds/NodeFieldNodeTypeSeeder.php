<?php

namespace July\Node\Seeds;

use App\Support\Arr;
use Database\Seeds\SeederBase;

class NodeFieldNodeTypeSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'node_field_node_type';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        // 所有字段
        $allFields = [];

        // 全局字段
        $globalFields = [];

        foreach (NodeFieldSeeder::getRecords() as $field) {
            $allFields[$field['id']] = Arr::only($field, [
                'label',
                'description',
                'field_meta',
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
