<?php

use Illuminate\Database\Seeder;
use App\Models;

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
                        'interface_values' => [
                            'name' => [
                                'zh' => '基础页面',
                            ],
                            'description' => [
                                'zh' => '如「关于我们页」，「联系我们页」等',
                            ],
                        ],
                    ],
                ],
                'fields' => ['content','h1'],
            ],
            [
                'type' => [
                    'truename' => 'article',
                    'config' => [
                        'langcode' => [
                            'interface_value' => 'zh',
                            'content_value' => 'en',
                        ],
                        'interface_values' => [
                            'name' => [
                                'zh' => '文章',
                            ],
                            'description' => [
                                'zh' => '添加一篇文章',
                            ],
                        ],
                    ],
                ],
                'fields' => ['content','h1','image_src','image_alt'],
            ],
            [
                'type' => [
                    'truename' => 'product',
                    'config' => [
                        'langcode' => [
                            'interface_value' => 'zh',
                            'content_value' => 'en',
                        ],
                        'interface_values' => [
                            'name' => [
                                'zh' => '产品',
                            ],
                            'description' => [
                                'zh' => '添加产品信息',
                            ],
                        ],
                    ],
                ],
                'fields' => ['content','h1','image_src','image_alt'],
            ],
        ];

        foreach ($nodeTypes as $type) {
            Models\NodeType::create($type['type']);

            $truename = $type['type']['truename'];
            array_unshift($type['fields'], 'title');
            foreach ($type['fields'] as $index => $field) {
                Models\NodeTypeNodeField::create([
                    'node_type' => $truename,
                    'node_field' => $field,
                    'delta' => $index,
                ]);
            }
        }
    }
}
