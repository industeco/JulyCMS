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
        $clang = config('jc.language.content');
        $data = [
            [
                'keyname' => 'multi_language',
                'group' => 'language',
                'label' => '多语言',
                'description' => '启用后可对内容进行翻译，可访问多语言页面。',
                'data' => [
                    'value_type' => 'boolean',
                    'value' => config('jc.language.multiple', false),
                ],
            ],
            [
                'keyname' => 'langcode.list',
                'group' => 'language',
                'label' => '列表',
                'description' => '可用语言列表',
                'data' => [
                    'value_type' => 'array',
                    'value' => config('jc.language.list'),
                ],
            ],
            [
                'keyname' => 'langcode.content',
                'group' => 'language',
                'label' => '内容默认语言',
                'description' => '添加内容时默认使用的语言。',
                'data' => [
                    'value_type' => 'string',
                    'value' => $clang,
                ],
            ],
            [
                'keyname' => 'langcode.page',
                'group' => 'language',
                'label' => '页面默认语言',
                'description' => '访问网站（非后台）页面时默认使用的语言。',
                'data' => [
                    'value_type' => 'string',
                    'value' => config('jc.language.page'),
                ],
            ],
            [
                'keyname' => 'url',
                'group' => 'basic',
                'label' => '首页网址',
                'description' => null,
                'data' => [
                    'value_type' => 'string',
                    'value' => config('app.url'),
                ],
            ],
            [
                'keyname' => 'email',
                'group' => 'basic',
                'label' => '默认邮箱',
                'description' => '联系表单的默认接收邮箱',
                'data' => [
                    'value_type' => 'string',
                    'value' => config('mail.to.address'),
                ],
            ],
            [
                'keyname' => 'owner',
                'group' => 'basic',
                'label' => '网站所有者（公司名）',
                'description' => null,
                'data' => [
                    'value_type' => 'string',
                    'value' => config('app.owner'),
                ],
            ],
            [
                'keyname' => 'field_group_settings',
                'group' => 'preference',
                'label' => '折叠/展开通用字段',
                'description' => '设置内容表单右侧通用字段面板默认折叠或展开',
                'data' => [
                    'value_type' => 'array',
                    'value' => [
                        '标签' => false,
                        '网址和模板' => false,
                        'META 信息' => false,
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
