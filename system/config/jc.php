<?php

return [
    // 站点基本设置
    'site' => [
        // 网站主体
        'subject' => env('SITE_SUBJECT', 'Wangke'),

        // 后端路由前缀
        'backend_route_prefix' => 'admin',

        // 是否允许通过实体路径访问
        'entity_path_accessible' => false,
    ],

    // 语言
    'language' => [
        // 多语言开关
        'multiple' => false,

        // 可用语言列表
        'list' => [
            'en' => [
                'accessible' => true,   // 前端可访问该语言页面
                'translatable' => true, // 内容可翻译为该语言
            ],
        ],

        // 后端界面默认语言
        'backend' => 'zh-Hans',

        // 后端编辑内容的默认语言
        'content' => 'en',

        // 前端页面默认语言
        'frontend' => 'en',
    ],

    // 主题
    'theme' => [
        'backend' => 'backend',
        'frontend' => 'frontend',
    ],

    // （后端）用户界面配置
    'ui' => [
        // 侧边栏
        'sidebar' => [
            // 菜单项
            'menu_items' => [
                [
                    'title' => '内容',
                    'icon' => 'create',
                    'route' => 'nodes.index',   // 路由名，或数组（格式：[路由名, 参数 1, 参数 2, ...]），下同
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
                        // [
                        //     'title' => '标签',
                        //     'icon' => null,
                        //     'route' => 'tags.index',
                        //     'children' => [],
                        // ],
                    ],
                ],
                [
                    'title' => '数据',
                    'icon' => 'view_column',
                    'route' => null,
                    'children' => [
                        [
                            'title' => '规格',
                            'icon' => null,
                            'route' => 'specs.index',
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
                            'title' => '基本设置',
                            'icon' => null,
                            'route' => ['configs.edit', 'site_information'],
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
                            'route' => ['configs.edit', 'user_preferences'],
                            'children' => [],
                        ],
                        [
                            'title' => '网址',
                            'icon' => null,
                            'route' => ['configs.edit', 'url'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 表单
    'form' => [
        // 富文本编辑器配置
        'editor' => [
            'default' => 'ckeditor',
            'ckeditor' => [
                'filebrowserImageBrowseUrl' => ['media.select'],
            ],
        ],

        // 全局字段分组
        'global_field_groups' => [
            'taxonomy' => [
                'label' => '分类和标签',   // 分组面板标题
                'expanded' => true,    // 是否默认展开
            ],
            'page_present' => [
                'label' => '网址和模板',
                'expanded' => true,
            ],
            'page_meta' => [
                'label' => 'META 信息',
                'expanded' => true,
            ],
        ],
    ],

    // 表单验证
    'validation' => [
        'file_bundles' => [
            'image' => [
                'png','jpg','jpeg','webp','bmp','svg','gif','ico',
            ],

            'file' => [
                'pdf', 'doc', 'ppt', 'xls', 'dwg',
            ],
        ],

        'patterns' => [
            'url' => '/^(\\/[a-z0-9\\-_]+)+\\.html$/',
            'twig' => '/^(\\/[a-z0-9\\-_]+)+(\\.html)?\\.twig$/',
            'email' => '\'email\'',
        ],
    ],

    'entity_field' => [
        'parameters_schema' => [
            'type' => [
                'cast' => 'string',
                'translatable' => false,
                'overwritable' => false,
                'default' => 'string',
            ],
            'default' => [
                'cast' => '@type',
                'translatable' => true,
                'overwritable' => true,
            ],
            'options' => [
                'cast' => 'array',
                'translatable' => true,
                'overwritable' => true,
            ],
            'placeholder' => [
                'cast' => '@type',
                'translatable' => true,
                'overwritable' => true,
            ],
            'maxlength' => [
                'cast' => 'int',
                'translatable' => false,
                'overwritable' => false,
            ],
            'required' => [
                'cast' => 'boolean',
                'translatable' => false,
                'overwritable' => true,
                'default' => false,
            ],
            'pattern' => [
                'cast' => 'string',
                'translatable' => false,
                'overwritable' => false,
            ],
            'file_bundle' => [
                'cast' => 'string',
                'translatable' => false,
                'overwritable' => false,
                // 'enumerators' => ['image', 'file'],
            ],
            'multiple' => [
                'cast' => 'boolean',
                'translatable' => false,
                'overwritable' => false,
                'default' => false,
            ],
            'helptext' => [
                'cast' => 'string',
                'translatable' => false,
                'overwritable' => true,
            ],
        ],
    ],
];
