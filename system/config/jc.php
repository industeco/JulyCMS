<?php

return [
    'admin' => [
        'prefix' => 'july/admin',
    ],

    'langcode' => [
        'permissions' => [
            'zh' => [
                'interface_value' => true,
                'admin_page' => true,
                'content_value' => false,
                'site_page' => false,
            ],
            'en' => [
                'interface_value' => false,
                'admin_page' => false,
                'content_value' => true,
                'site_page' => true,
            ],
        ],
        'interface_value' => 'zh',
        'admin_page' => 'zh',
        'content_value' => 'en',
        'site_page' => 'en',
    ],

    'multi_language' => false,

    'editor_config' => [
        'ckeditor' => [
            'fillEmptyBlocks' => false,
            'allowedContent' => true,
            'coreStyles_bold' => [
                'element' => 'b',
                'overrides' => 'strong',
            ],
            'toolbarGroups' => [
                [ 'name' => 'document', 'groups' => [ 'mode', 'document', 'doctools' ] ],
                [ 'name' => 'clipboard', 'groups' => [ 'clipboard', 'undo' ] ],
                [ 'name' => 'styles', 'groups' => [ 'styles' ] ],
                [ 'name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ] ],
                [ 'name' => 'links', 'groups' => [ 'links' ] ],
                [ 'name' => 'paragraph', 'groups' => [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] ],
                [ 'name' => 'insert', 'groups' => [ 'insert' ] ],
                [ 'name' => 'forms', 'groups' => [ 'forms' ] ],
                [ 'name' => 'tools', 'groups' => [ 'tools' ] ],
                // [ 'name' => 'others', 'groups' => [ 'others' ] ],
                // [ 'name' => 'editing', 'groups' => [ 'find', 'selection', 'spellchecker', 'editing' ] ],
                // [ 'name' => 'colors', 'groups' => [ 'colors' ] ],
                // [ 'name' => 'about', 'groups' => [ 'about' ] ],
            ],
            'removeButtons' => 'Underline,Styles,Strike,Italic,Indent,Outdent,Blockquote,About,SpecialChar,HorizontalRule,Scayt,Cut,Copy,Paste,PasteText,PasteFromWord',
            'filebrowserImageBrowseUrl' => '/july/admin/medias/select',
            'image_previewText' => ' ',
        ],
    ],

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

    'sidebar_menu' => [
        [
            'title' => '内容',
            'icon' => 'create',
            'route' => 'nodes.index',
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
                    'title' => '基本设置',
                    'icon' => null,
                    'route' => 'configs.basic.edit',
                    'children' => [],
                ],
                [
                    'title' => '语言',
                    'icon' => null,
                    'route' => 'configs.language.edit',
                    'children' => [],
                ],
            ],
        ],
    ],
];
