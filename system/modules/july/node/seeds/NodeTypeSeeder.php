<?php

namespace July\Node\Seeds;

use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;

class NodeTypeSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['node_types'];

    /**
     * 获取 node_types 表数据
     *
     * @return array
     */
    protected function getNodeTypesTableRecords()
    {
        $records = [
            [
                'id' => 'basic',
                'label' => '基础页面',
                'description' => '可用于静态页面，如「关于我们」页等；该类型不可删除。',
                'is_reserved' => true,
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
}
