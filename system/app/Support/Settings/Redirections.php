<?php

namespace App\Support\Settings;

class Redirections extends SettingGroupBase
{
    /**
     * 配置组名称
     *
     * @var string
     */
    protected $name = 'redirections';

    /**
     * 配置组标题
     *
     * @var string
     */
    protected $title = '重定向';

    /**
     * 配置项
     *
     * @var array
     */
    protected $items = [
        'site.redirections' => [
            'key' => 'app.field_groups',
            'label' => '重定向设置',
            'description' => '添加 301，302 重定向',
        ],
    ];
}
