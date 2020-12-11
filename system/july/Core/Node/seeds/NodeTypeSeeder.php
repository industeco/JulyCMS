<?php

namespace July\Core\Node\seeds;

use Illuminate\Support\Facades\Date;
use July\Base\SeederBase;

class NodeTypeSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['node_types', 'node_field_node_type'];

    /**
     * 获取 node_types 表数据
     *
     * @return array
     */
    protected function getNodeTypesRecords()
    {
        $records = [
            [
                'id' => 'basic',
                'is_necessary' => true,
                'label' => '基础页面',
                'description' => '可用于静态页面，如「关于我们」页等；该类型不可删除。',
            ],
            [
                'id' => 'list',
                'label' => '列表页',
                'description' => '用于生成列表页面。',
            ],
            [
                'id' => 'product',
                'label' => '产品页',
                'description' => '用于生成产品页面。',
            ],
            [
                'id' => 'article',
                'label' => '文章页',
                'description' => '用于生成文章页面。',
            ],
        ];

        $share = [
            'langcode' => langcode('content.default'),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
        }, $records);
    }

    /**
     * 获取 node_field_node_type 表数据
     *
     * @return array
     */
    protected function getNodeFieldNodeTypeRecords()
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
