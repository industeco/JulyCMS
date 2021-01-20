<?php

namespace App\Modules\Settings;

class UserInterface extends PreferenceGroupBase
{
    /**
     * 配置组名称
     *
     * @var string
     */
    protected $name = 'user_interface';

    /**
     * 配置组标题
     *
     * @var string
     */
    protected $title = '界面偏好';

    /**
     * 配置项
     *
     * @var array
     */
    protected $items = [
        'app.field_groups' => [
            'key' => 'app.field_groups',
            'label' => '字段分组',
            'description' => '配置全局字段分组面板（位于表单右侧，用于分组管理全局字段）',
        ],
    ];
}
