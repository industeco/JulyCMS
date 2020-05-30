<?php

use Illuminate\Database\Seeder;
use App\Models\NodeField;
use Illuminate\Support\Facades\DB;

class NodeFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getData() as $record) {
            DB::table('node_fields')->insert($record);
        }

        foreach ($this->getConfigData() as $record) {
            DB::table('configs')->insert($record);
        }
    }

    protected function getData()
    {
        return [
            [
                'truename' => 'title',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => true,
                'is_global' => false,
            ],
            [
                'truename' => 'url',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'template',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'meta_title',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'meta_description',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'meta_keywords',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'meta_canonical',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'is_global' => true,
            ],
            [
                'truename' => 'content',
                'field_type' => 'html',
                'is_preset' => false,
                'is_searchable' => true,
                'is_global' => false,
            ],
        ];
    }

    protected function getConfigData()
    {
        $configs = [
            [
                'keyname' => 'node_field.title',
                'group' => null,
                'name' => '标题',
                'description' => '内容标题，通常用作链接文字。',
                'data' => [
                    'length' => 200,
                    'required' => true,
                    'search_weight' => 10,
                ],
            ],
            [
                'keyname' => 'node_field.url',
                'group' => '网址和模板',
                'name' => '网址',
                'description' => null,
                'data' => [
                    'length' => 200,
                    'pattern' => 'url',
                    'placeholder' => [
                        'en' => '/index.html',
                    ],
                ],
            ],
            [
                'keyname' => 'node_field.template',
                'group' => '网址和模板',
                'name' => '模板',
                'description' => 'twig 模板，用于生成页面。',
                'data' => [
                    'length' => 200,
                    'pattern' => 'twig',
                    'placeholder' => [
                        'en' => '/home.twig',
                    ],
                ],
            ],
            [
                'keyname' => 'node_field.meta_title',
                'group' => 'META 信息',
                'name' => 'META 标题',
                'description' => null,
                'data' => [
                    'length' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_description',
                'group' => 'META 信息',
                'name' => 'META 描述',
                'description' => '描述应控制在 160 个字符以内。',
                'data' => [
                    'length' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_keywords',
                'group' => 'META 信息',
                'name' => 'META 关键词',
                'description' => '关键词应控制在 160 个字符以内。',
                'data' => [
                    'length' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_canonical',
                'group' => 'META 信息',
                'name' => 'META 权威页面',
                'description' => null,
                'data' => [
                    'length' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.content',
                'group' => null,
                'name' => '内容',
                'description' => null,
                'data' => [
                    'search_weight' => 1,
                ],
            ],
        ];

        return array_map(function($config) {
            $config['data'] = json_encode($config['data'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            return $config;
        }, $configs);
    }
}
