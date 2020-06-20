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
        // 'fillEmptyBlocks' => false,
        // 'allowedContent' => true,
        // 'image_previewText' => ' ',
        'filebrowserImageBrowseUrl' => ['media.select'],
        // 'coreStyles_bold' => [
        //     'element' => 'b',
        //     'overrides' => 'strong',
        // ],
        // 'toolbarGroups' => [
        //     [ 'name' => 'document', 'groups' => [ 'mode', 'document', 'doctools' ] ],
        //     [ 'name' => 'clipboard', 'groups' => [ 'clipboard', 'undo' ] ],
        //     [ 'name' => 'styles', 'groups' => [ 'styles' ] ],
        //     [ 'name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ] ],
        //     [ 'name' => 'links', 'groups' => [ 'links' ] ],
        //     [ 'name' => 'paragraph', 'groups' => [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] ],
        //     [ 'name' => 'insert', 'groups' => [ 'insert' ] ],
        //     [ 'name' => 'forms', 'groups' => [ 'forms' ] ],
        //     [ 'name' => 'tools', 'groups' => [ 'tools' ] ],
        //     // [ 'name' => 'others', 'groups' => [ 'others' ] ],
        //     // [ 'name' => 'editing', 'groups' => [ 'find', 'selection', 'spellchecker', 'editing' ] ],
        //     // [ 'name' => 'colors', 'groups' => [ 'colors' ] ],
        //     // [ 'name' => 'about', 'groups' => [ 'about' ] ],
        // ],
        // 'removeButtons' => 'Underline,Styles,Strike,Italic,Indent,Outdent,Blockquote,About,SpecialChar,HorizontalRule,Scayt,Cut,Copy,Paste,PasteText,PasteFromWord',
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

    // 侧边栏
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
];
