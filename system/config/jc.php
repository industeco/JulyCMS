<?php

return [
    // 后台路由前缀
    'admin_prefix' => 'admin',

    // 多语言开关
    'multi_language' => false,

    // 语言设置
    'langcode' => [
        'list' => [
            'en' => [
                'accessible' => true,
                'translatable' => true,
            ],
        ],
        'content' => 'en',
        'page' => 'en',
        'admin_page' => 'zh-Hans',
    ],

    // 默认主题
    'theme' => [
        'background' => 'admin',
        'foreground' => 'default',
    ],

    // 编辑器配置
    'ckeditor' => [
        'filebrowserImageBrowseUrl' => ['media.select'],
    ],

    // 表单验证规则
    'rules' => [
        'file_type' => [
            'image' => [
                'png','jpg','jpeg','webp','bmp','svg','gif','ico',
            ],
            'file' => [
                'pdf', 'doc', 'ppt', 'xls', 'dwg',
            ],
        ],

        'pattern' => [
            'url' => '/^(\\/[a-z0-9\\-_]+)+\\.html$/',
            'twig' => '/^(\\/[a-z0-9\\-_]+)+(\\.html)?\\.twig$/',
            'email' => '\'email\'',
        ],
    ],

    // 侧边栏配置
    'sidebar_menu' => [
        [
            'title' => '内容',
            'icon' => 'create',
            'route' => 'nodes.index',   // 路由名，下同
            'children' => [],
        ],
        [
            'title' => '类型',
            'icon' => 'category',
            'route' => 'node_types.index',
            'children' => [],
        ],
        [
            'title' => '结构',
            'icon' => 'device_hub',
            'route' => null,
            'children' => [
                [
                    'title' => '目录',
                    'icon' => null,
                    'route' => 'catalogs.index',
                    'children' => [],
                ],
                [
                    'title' => '标签',
                    'icon' => null,
                    'route' => 'tags.index',
                    'children' => [],
                ],
            ],
        ],
        [
            'title' => '文件',
            'icon' => 'folder',
            'route' => 'media.index',
            'children' => [],
        ],
        [
            'title' => '设置',
            'icon' => 'settings',
            'route' => null,
            'children' => [
                [
                    'title' => '基础',
                    'icon' => null,
                    'route' => ['configs.edit', 'basic'],
                    'children' => [],
                ],
                [
                    'title' => '语言',
                    'icon' => null,
                    'route' => ['configs.edit', 'language'],
                    'children' => [],
                ],
                [
                    'title' => '偏好',
                    'icon' => null,
                    'route' => ['configs.edit', 'preference'],
                    'children' => [],
                ],
            ],
        ],
    ],

    'field_attributes' => [
        'helptext' => [
            'value_type' => 'string',
        ],
        'max' => [
            'value_type' => 'int',
        ],
        'maxlengt' => [
            'value_type' => 'int',
        ],
        'min' => [
            'value_type' => 'int',
        ],
        'minlength' => [
            'value_type' => 'int',
        ],
        'multiple' => [
            'value_type' => 'boolean',
        ],
        'options' => [
            'value_type' => 'array',
        ],
        'pattern' => [
            'value_type' => 'string',
        ],
        'placeholder' => [
            'value_type' => 'string',
        ],
        'readonly' => [
            'value_type' => 'boolean',
        ],
        'required' => [
            'value_type' => 'boolean',
        ],
        'size' => [
            'value_type' => 'int',
        ],
        'step' => [
            'value_type' => 'int',
        ],
        'value' => [
            'value_type' => 'mixed',
        ],
    ],
];
