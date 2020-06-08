<?php

use Illuminate\Database\Seeder;
use App\Models\NodeField;
use Illuminate\Support\Facades\Date;
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
        foreach ($this->getNodeFieldData() as $record) {
            DB::table('node_fields')->insert($record);
        }

        foreach ($this->getParametersData() as $record) {
            DB::table('field_parameters')->insert($record);
        }
    }

    protected function getNodeFieldData()
    {
        $data = [
            [
                'truename' => 'title',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => false,
                'is_searchable' => true,
                'weight' => 10,
                'group' => null,
                'label' => '标题',
                'description' => '内容标题，通常用作链接文字。',
            ],
            [
                'truename' => 'summary',
                'field_type' => 'html',
                'is_preset' => true,
                'is_global' => false,
                'is_searchable' => false,
                'weight' => 1,
                'group' => null,
                'label' => '内容摘要',
                'description' => null,
            ],
            [
                'truename' => 'content',
                'field_type' => 'html',
                'is_preset' => true,
                'is_global' => false,
                'is_searchable' => true,
                'weight' => 1,
                'group' => null,
                'label' => '内容',
                'description' => null,
            ],
            [
                'truename' => 'url',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => '网址和模板',
                'label' => '网址',
                'description' => null,
            ],
            [
                'truename' => 'template',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => '网址和模板',
                'label' => '模板',
                'description' => 'twig 模板，用于生成页面。',
            ],
            [
                'truename' => 'meta_title',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => 'META 信息',
                'label' => 'META 标题',
                'description' => null,
            ],
            [
                'truename' => 'meta_description',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => 'META 信息',
                'label' => 'META 描述',
                'description' => '描述应控制在 160 个字符以内。',
            ],
            [
                'truename' => 'meta_keywords',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => 'META 信息',
                'label' => 'META 关键词',
                'description' => '关键词应控制在 160 个字符以内。',
            ],
            [
                'truename' => 'meta_canonical',
                'field_type' => 'text',
                'is_preset' => true,
                'is_global' => true,
                'is_searchable' => false,
                'weight' => 1,
                'group' => 'META 信息',
                'label' => 'META 权威页面',
                'description' => null,
            ],
        ];

        $langcode = config('jc.langcode.content');
        return array_map(function($record) use($langcode) {
            return array_merge($record, [
                'langcode' => $langcode,
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ]);
        }, $data);
    }

    protected function getParametersData()
    {
        $parameters = [
            [
                'keyname' => 'node_field.title',
                'data' => [
                    'required' => true,
                    'max' => 200,
                ],
            ],
            [
                'keyname' => 'node_field.summary',
                'data' => [
                    'max' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.content',
                'data' => [],
            ],
            [
                'keyname' => 'node_field.url',
                'data' => [
                    'length' => 200,
                    'pattern' => 'url',
                    'placeholder' => '/index.html',
                ],
            ],
            [
                'keyname' => 'node_field.template',
                'data' => [
                    'max' => 200,
                    'pattern' => 'twig',
                    'placeholder' => '/home.twig',
                ],
            ],
            [
                'keyname' => 'node_field.meta_title',
                'data' => [
                    'max' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_description',
                'data' => [
                    'max' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_keywords',
                'data' => [
                    'max' => 255,
                ],
            ],
            [
                'keyname' => 'node_field.meta_canonical',
                'data' => [
                    'max' => 255,
                ],
            ],
        ];

        $langcode = config('jc.langcode.content');
        return array_map(function($record) use($langcode) {
            $record['data'] = json_encode($record['data'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $record['langcode'] = $langcode;
            return $record;
        }, $parameters);
    }
}
