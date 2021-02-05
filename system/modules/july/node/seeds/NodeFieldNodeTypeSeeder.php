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
        // 所有字段信息
        $allFields = collect((new NodeFieldSeeder)->getNodeFieldsTableRecords())
            ->keyBy('id')
            ->map(function(array $field) {
                return Arr::only($field, [
                    'label',
                    'description',
                    'is_required',
                    'helpertext',
                    'default_value',
                    'options',
                    'rules',
                ]);
            }, true);

        // 全部预设字段
        // $reserved = ['title'];
        $reserved = [];

        // 全局字段
        // $global = ['url', 'view', 'meta_title', 'meta_keywords', 'meta_description', 'meta_canonical'];
        $global = ['url', 'meta_title', 'meta_keywords', 'meta_description', 'meta_canonical'];

        // 类型关联字段
        $molds = [
            'basic' => ['content'],
            'list' => ['content'],
            'product' => ['summary', 'content', 'image_src', 'image_alt'],
            'article' => ['summary', 'content', 'image_src', 'image_alt'],
        ];

        // 生成记录
        $records = [];
        foreach ($molds as $id => $fields) {
            $fields = array_merge($reserved, $fields, $global);
            foreach ($fields as $index => $field) {
                $records[] = $allFields->get($field) + [
                    'mold_id' => $id,
                    'field_id' => $field,
                    'delta' => $index,
                ];
            }
        }

        return $records;
    }
}
