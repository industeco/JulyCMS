<?php

use Illuminate\Database\Seeder;
use App\Models;
use Illuminate\Support\Facades\DB;

class NodeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nodeTypes = [
            [
                'type' => [
                    'truename' => 'basic',
                    'config' => [
                        'langcode' => [
                            'interface_value' => 'zh',
                            'content_value' => 'en',
                        ],
                        'name' => [
                            'zh' => '基础页面',
                        ],
                        'description' => [
                            'zh' => '如「关于我们页」，「联系我们页」等',
                        ],
                    ],
                ],
                'fields' => ['title','content','h1'],
            ],
            [
                'type' => [
                    'truename' => 'article',
                    'config' => [
                        'langcode' => [
                            'interface_value' => 'zh',
                            'content_value' => 'en',
                        ],
                        'name' => [
                            'zh' => '文章',
                        ],
                        'description' => [
                            'zh' => '添加一篇文章',
                        ],
                    ],
                ],
                'fields' => ['title','content','h1','image_src','image_alt'],
            ],
            [
                'type' => [
                    'truename' => 'product',
                    'config' => [
                        'langcode' => [
                            'interface_value' => 'zh',
                            'content_value' => 'en',
                        ],
                        'name' => [
                            'zh' => '产品',
                        ],
                        'description' => [
                            'zh' => '添加产品信息',
                        ],
                    ],
                ],
                'fields' => ['title','content','h1','image_src','image_alt'],
            ],
        ];

        DB::transaction(function() use($nodeTypes) {
            foreach ($nodeTypes as $nodeType) {
                $type = $nodeType['type'];
                $type['config'] = json_encode($type['config'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                DB::table('node_types')->insert($type);

                foreach ($nodeType['fields'] as $index => $field) {
                    DB::table('node_field_node_type')->insert([
                        'node_type' => $type['truename'],
                        'node_field' => $field,
                        'delta' => $index,
                    ]);
                }
            }
        });
    }
}
