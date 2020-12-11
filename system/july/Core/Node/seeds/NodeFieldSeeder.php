<?php

namespace July\Core\Node\seeds;

use Illuminate\Support\Facades\Date;
use July\Base\SeederBase;
use July\Core\Node\NodeField;

class NodeFieldSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['node_fields', 'field_parameters'];

    /**
     * 获取 node_fields 表数据
     *
     * @return array
     */
    protected function getNodeFieldsRecords()
    {
        $records = [
            // [
            //     'id' => 'url',
            //     'field_type_id' => 'text',
            //     'is_necessary' => true,
            //     'is_searchable' => false,
            //     'preset_type' => 2,
            //     'global_group' => 'page_present',
            //     'label' => '网址',
            //     'description' => null,
            // ],
            // [
            //     'id' => 'template',
            //     'field_type_id' => 'text',
            //     'is_necessary' => true,
            //     'is_searchable' => false,
            //     'preset_type' => 2,
            //     'global_group' => 'page_present',
            //     'label' => '模板',
            //     'description' => 'twig 模板，用于生成页面。',
            // ],
            [
                'id' => 'meta_title',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => false,
                'preset_type' => 2,
                'global_group' => 'page_meta',
                'label' => 'META 标题',
                'description' => null,
            ],
            [
                'id' => 'meta_keywords',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => false,
                'preset_type' => 2,
                'global_group' => 'page_meta',
                'label' => 'META 关键词',
                'description' => '关键词应控制在 160 个字符以内。',
            ],
            [
                'id' => 'meta_description',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => false,
                'preset_type' => 2,
                'global_group' => 'page_meta',
                'label' => 'META 描述',
                'description' => '描述应控制在 160 个字符以内。',
            ],
            [
                'id' => 'meta_canonical',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => false,
                'preset_type' => 2,
                'global_group' => 'page_meta',
                'label' => 'META 权威页面',
                'description' => null,
            ],
            [
                'id' => 'title',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => true,
                'weight' => 10,
                'preset_type' => 1,
                'label' => '标题',
                'description' => '内容标题，通常用作链接文字。',
            ],
            [
                'id' => 'content',
                'field_type_id' => 'html',
                'is_necessary' => true,
                'is_searchable' => true,
                'preset_type' => 0,
                'label' => '内容',
                'description' => null,
            ],
            [
                'id' => 'summary',
                'field_type_id' => 'text',
                'is_necessary' => true,
                'is_searchable' => true,
                'preset_type' => 0,
                'label' => '内容摘要',
                'description' => null,
            ],
            [
                'id' => 'image_src',
                'field_type_id' => 'file',
                'is_necessary' => false,
                'is_searchable' => false,
                'preset_type' => 0,
                'label' => '主图',
                'description' => null,
            ],
            [
                'id' => 'image_alt',
                'field_type_id' => 'text',
                'is_necessary' => false,
                'is_searchable' => false,
                'preset_type' => 0,
                'label' => '主图 alt',
                'description' => null,
            ],
        ];

        // $langcode = langcode('content.default');
        foreach ($records as $index => &$record) {
            $record += [
                // 'delta' => $index,
                // 'langcode' => $langcode,
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ];
        }
        unset($record);

        return $records;
    }

    /**
     * 获取 field_parameters 表数据
     *
     * @return array
     */
    protected function getFieldParametersRecords()
    {
        $records = [
            // [
            //     'field_id' => 'url',
            //     'parameters' => [
            //         'maxlength' => 255,
            //         'pattern' => 'url',
            //         'placeholder' => '/index.html',
            //     ],
            // ],
            // [
            //     'field_id' => 'template',
            //     'parameters' => [
            //         'maxlength' => 200,
            //         'pattern' => 'twig',
            //         'placeholder' => '/home.twig',
            //     ],
            // ],
            [
                'field_id' => 'meta_title',
                'parameters' => [
                    'maxlength' => 255,
                ],
            ],
            [
                'field_id' => 'meta_description',
                'parameters' => [
                    'maxlength' => 255,
                ],
            ],
            [
                'field_id' => 'meta_keywords',
                'parameters' => [
                    'maxlength' => 255,
                ],
            ],
            [
                'field_id' => 'meta_canonical',
                'parameters' => [
                    'maxlength' => 255,
                ],
            ],
            [
                'field_id' => 'title',
                'parameters' => [
                    'required' => true,
                    'maxlength' => 200,
                ],
            ],
            [
                'field_id' => 'summary',
                'parameters' => [
                    'maxlength' => 255,
                ],
            ],
            [
                'field_id' => 'image_src',
                'parameters' => [
                    'file_bundle' => 'image',
                ],
            ],
            [
                'field_id' => 'image_alt',
                'parameters' => [
                    'maxlength' => 200,
                ],
            ],
        ];

        $share = [
            // 'entity_name' => 'node_field',
            'langcode' => langcode('content.default'),
        ];

        return array_map(function($record) use($share) {
            $record += $share;
            $record['parameters'] = serialize($record['parameters'] ?? []);
            return $record;
        }, $records);
    }

    public static function postSeeding()
    {
        foreach (NodeField::all() as $field) {
            $field->tableUp();
        }
    }
}
