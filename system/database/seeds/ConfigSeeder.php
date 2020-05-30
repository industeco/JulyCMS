<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getConfigData() as $record) {
            DB::table('configs')->insert($record);
        }
    }

    protected function getConfigData()
    {
        $clang = config('jc.langcode.content');
        $data = [
            [
                'keyname' => 'config.multi_language',
                'group' => 'language',
                'name' => '多语言',
                'description' => '启用后可对内容进行翻译，可访问多语言页面。',
                'data' => [
                    'value_type' => 'boolean',
                    'value' => config('jc.multi_language', false),
                ],
            ],
            [
                'keyname' => 'config.langcode.permissions',
                'group' => 'language',
                'name' => '可用语言',
                'description' => '内容翻译和访问多语言页面时可用的语言。',
                'data' => [
                    'value_type' => 'array',
                    'value' => config('jc.langcode.permissions'),
                ],
            ],
            [
                'keyname' => 'config.langcode.content',
                'group' => 'language',
                'name' => '内容默认语言',
                'description' => '添加内容时默认使用的语言。',
                'data' => [
                    'value_type' => 'string',
                    'value' => $clang,
                ],
            ],
            [
                'keyname' => 'config.langcode.page',
                'group' => 'language',
                'name' => '页面默认语言',
                'description' => '访问网站页面（非后台）时默认使用的语言。',
                'data' => [
                    'value_type' => 'string',
                    'value' =>  config('jc.langcode.page'),
                ],
            ],
            [
                'keyname' => 'config.url',
                'group' => 'basic',
                'name' => '首页网址',
                'description' => null,
                'data' => [
                    'value_type' => 'string',
                    'value' => config('app.url'),
                ],
            ],
            [
                'keyname' => 'config.email',
                'group' => 'basic',
                'name' => '默认邮箱',
                'description' => '联系表单的默认接收邮箱',
                'data' => [
                    'value_type' => 'string',
                    'value' => config('mail.to.address'),
                ],
            ],
            [
                'keyname' => 'config.owner',
                'group' => 'basic',
                'name' => '网站所有者（公司名）',
                'description' => null,
                'data' => [
                    'value_type' => 'string',
                    'value' => config('app.owner'),
                ],
            ],
            [
                'keyname' => 'config.collapse_global_fields',
                'group' => 'background_interface',
                'name' => '通用字段折叠设置',
                'description' => '设置内容编辑页右侧通用字段默认折叠或展开',
                'data' => [
                    'value_type' => 'array',
                    'value' => [
                        [
                            'group' => '网址和模板',
                            'collapsed' => true,
                        ],
                        [
                            'group' => 'META 信息',
                            'collapsed' => true,
                        ],
                    ],
                ],
            ],
        ];

        return array_map(function($record) {
            $record['data'] = json_encode($record['data'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            return $record;
        }, $data);
    }
}
