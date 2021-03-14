<?php

namespace July\Node\Seeds;

use App\EntityField\FieldTypes;
use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;
use July\Node\NodeField;

class NodeFieldSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'node_fields';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        $records = [
            [
                'id' => 'url',
                'field_type' => FieldTypes\PathAlias::class,
                'label' => '网址',
                'description' => null,
                'is_reserved' => true,
                'is_global' => true,
                'field_group' => '网址',
            ],
            [
                'id' => 'meta_title',
                'field_type' => FieldTypes\Input::class,
                'label' => '标题',
                'description' => '标题建议控制在 60 个字符以内。',
                'is_reserved' => true,
                'is_global' => true,
                'field_group' => 'SEO 信息',
            ],
            [
                'id' => 'meta_keywords',
                'field_type' => FieldTypes\Text::class,
                'label' => '关键词',
                'description' => '关键词建议控制在 160 个字符以内。',
                'is_reserved' => true,
                'is_global' => true,
                'field_group' => 'SEO 信息',
            ],
            [
                'id' => 'meta_description',
                'field_type' => FieldTypes\Text::class,
                'label' => '描述',
                'description' => '描述建议控制在 160 个字符以内。',
                'is_reserved' => true,
                'is_global' => true,
                'field_group' => 'SEO 信息',
            ],
            [
                'id' => 'meta_canonical',
                'field_type' => FieldTypes\Url::class,
                'label' => '权威页面',
                'description' => null,
                'is_reserved' => true,
                'is_global' => true,
                'field_group' => 'SEO 信息',
            ],
            [
                'id' => 'content',
                'field_type' => FieldTypes\Html::class,
                'label' => '内容',
                'description' => null,
                'weight' => 1,
            ],
            [
                'id' => 'summary',
                'field_type' => FieldTypes\Text::class,
                'label' => '内容摘要',
                'description' => null,
                'weight' => 1,
            ],
            [
                'id' => 'image_src',
                'field_type' => FieldTypes\Image::class,
                'label' => '主图',
                'description' => null,
            ],
            [
                'id' => 'image_alt',
                'field_type' => FieldTypes\Input::class,
                'label' => '主图 alt',
                'description' => null,
            ],
        ];

        $share = [
            'langcode' => langcode('content.default'),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return array_merge($record, $share, [
                'field_meta' => isset($record['field_meta']) ? serialize($record['field_meta']) : null,
            ]);
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
