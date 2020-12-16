<?php

namespace July\Core\Config\seeds;

use App\Database\SeederBase;

class ConfigsSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = [
        'configs', 'config__value'
    ];

    /**
     * 获取 configs 表数据
     *
     * @return array
     */
    protected function getConfigsRecords()
    {
        return [
            [
                'id' => 'jc.language.multiple',
                'group' => 'language',
                'label' => '多语言开关',
                'description' => '启用后可对内容进行翻译，可访问多语言页面。',
            ],
            [
                'id' => 'jc.language.list',
                'group' => 'language',
                'label' => '可用语言',
                'description' => '可用语言及其配置',
            ],
            [
                'id' => 'jc.language.content',
                'group' => 'language',
                'label' => '内容默认语言',
                'description' => '后台添加内容的默认语言。',
            ],
            [
                'id' => 'jc.language.frontend',
                'group' => 'language',
                'label' => '前端默认语言',
                'description' => '前端页面的默认语言。',
            ],
            [
                'id' => 'app.url',
                'group' => 'site_information',
                'label' => '首页网址',
                'description' => '首页网址，只读（必要时可在 .env 文件中修改，键名：APP_URL）。',
                // 'is_readonly' => true,
            ],
            [
                'id' => 'jc.site.subject',
                'group' => 'site_information',
                'label' => '企业（个人）名称',
                'description' => '网站所服务的主体（企业或个人）的名称',
            ],
            [
                'id' => 'mail.to.address',
                'group' => 'site_information',
                'label' => '网站邮箱',
                'description' => '联系表单的默认接收邮箱',
            ],
            [
                'id' => 'jc.form.global_field_groups',
                'group' => 'user_preferences',
                'label' => '字段分组',
                'description' => '配置全局字段分组面板（位于表单右侧，用于分组管理全局字段）',
            ],
        ];
    }

    /**
     * 获取 configs 表数据
     *
     * @return array
     */
    protected function getConfigValueRecords()
    {
        $records = [
            // [
            //     'config_id' => 'jc.preference.field_group_settings',
            //     'value' => [
            //         'taxonomy' => false,
            //         'page_access' => false,
            //         'page_meta' => false,
            //     ],
            // ],
        ];

        return array_map(function($record) {
            $record['value'] = serialize($record['value']);
            return $record;
        }, $records);
    }
}
