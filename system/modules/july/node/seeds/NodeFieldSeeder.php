<?php

namespace July\Node\Seeds;

use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;
use July\Node\NodeField;

class NodeFieldSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['node_fields'];

    /**
     * 获取 node_fields 表数据
     *
     * @return array
     */
    public function getNodeFieldsTableRecords()
    {
        $records = [
            [
                'id' => 'url',
                'field_type_id' => 'path_alias',
                'is_reserved' => true,
                'is_global' => true,
                'group_title' => '网址',
                'label' => '网址',
                'description' => null,
            ],
            // [
            //     'id' => 'view',
            //     'field_type_id' => 'reserved.view',
            //     'is_reserved' => true,
            //     'is_global' => true,
            //     'group_title' => '网址和模板',
            //     'label' => '模板',
            //     'description' => 'twig 模板',
            // ],
            [
                'id' => 'meta_title',
                'field_type_id' => 'input',
                'is_reserved' => true,
                'is_global' => true,
                'group_title' => 'SEO 信息',
                'maxlength' => 60,
                'label' => '标题',
                'description' => '标题建议控制在 60 个字符以内。',
            ],
            [
                'id' => 'meta_keywords',
                'field_type_id' => 'text',
                'is_reserved' => true,
                'is_global' => true,
                'group_title' => 'SEO 信息',
                'maxlength' => 160,
                'label' => '关键词',
                'description' => '关键词建议控制在 160 个字符以内。',
            ],
            [
                'id' => 'meta_description',
                'field_type_id' => 'text',
                'is_reserved' => true,
                'is_global' => true,
                'group_title' => 'SEO 信息',
                'maxlength' => 160,
                'label' => '描述',
                'description' => '描述建议控制在 160 个字符以内。',
            ],
            [
                'id' => 'meta_canonical',
                'field_type_id' => 'url',
                'is_reserved' => true,
                'is_global' => true,
                'group_title' => 'SEO 信息',
                'label' => '权威页面',
                'description' => null,
            ],
            // [
            //     'id' => 'title',
            //     'field_type_id' => 'input',
            //     'is_reserved' => true,
            //     'search_weight' => 10,
            //     'label' => '标题',
            //     'description' => '内容标题，通常用作链接文字。',
            //     'is_required' => true,
            // ],
            [
                'id' => 'content',
                'field_type_id' => 'html',
                'search_weight' => 1,
                'label' => '内容',
                'description' => null,
            ],
            [
                'id' => 'summary',
                'field_type_id' => 'text',
                'search_weight' => 1,
                'label' => '内容摘要',
                'description' => null,
            ],
            [
                'id' => 'image_src',
                'field_type_id' => 'image',
                'label' => '主图',
                'description' => null,
            ],
            [
                'id' => 'image_alt',
                'field_type_id' => 'input',
                'label' => '主图 alt',
                'description' => null,
            ],
        ];

        $now = Date::now();
        $share = [
            'langcode' => langcode('content.default'),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
        }, $records);
    }

    /**
     * {@inheritdoc}
     */
    public static function afterSeeding()
    {
        foreach (NodeField::all() as $field) {
            $field->tableUp();
        }
    }
}
