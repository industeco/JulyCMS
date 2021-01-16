<?php

namespace App\Settings;

class SiteInformation extends SettingGroupBase
{
    /**
     * 配置组名称
     *
     * @var string
     */
    protected $name = 'site_information';

    /**
     * 配置项
     *
     * @var array
     */
    protected $items = [
        'app.url' => [
            'key' => 'app.url',
            'label' => '网址',
            'description' => '首页网址',
        ],

        'site.subject' => [
            'key' => 'site.subject',
            'label' => '企业（个人）名称',
            'description' => '网站所服务的主体（企业或个人）的名称',
        ],

        'mail.to.address' => [
            'key' => 'mail.to.address',
            'label' => '网站邮箱',
            'description' => '联系表单的默认接收邮箱',
        ],
    ];
}
