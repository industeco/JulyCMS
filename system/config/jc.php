<?php

return [
    'languages' => ['zh', 'en'],
    'content_lang' => 'en',
    'interface_lang' => 'zh',
    'site_page_lang' => 'en',
    'admin_page_lang' => 'zh',

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
            'filebrowserImageBrowseUrl' => '/admin/medias/select',
            'image_previewText' => ' ',
        ],
    ],

    'rules' => [
        'file_type' => [
            'image' => [
                'png','jpg','jpeg','webp','bmp','svg','gif','ico',
            ],
            'pdf' => [
                'pdf',
            ],
        ],

        'pattern' => [
            'url' => '/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/',
            'twig' => '/^(\\/[a-z0-9\\-_]+)+(\\.html)?\\.twig$/',
            'email' => '\'email\'',
        ],
    ],

    'sidebar_menu' => [
        [
            'title' => '内容',
            'icon' => 'create',
            'url' => '/admin/nodes',
            'children' => [],
        ],
        [
            'title' => '类型',
            'icon' => 'category',
            'url' => '/admin/node_types',
            'children' => [],
        ],
        [
            'title' => '结构',
            'icon' => 'device_hub',
            'url' => null,
            'children' => [
                [
                    'title' => '目录',
                    'icon' => null,
                    'url' => '/admin/catalogs',
                    'children' => [],
                ],
                [
                    'title' => '标签',
                    'icon' => null,
                    'url' => '/admin/tags',
                    'children' => [],
                ],
            ],
        ],
        [
            'title' => '文件',
            'icon' => 'folder',
            'url' => '/admin/medias',
            'children' => [],
        ],
        [
            'title' => '设置',
            'icon' => 'settings',
            'url' => null,
            'children' => [
                [
                    'title' => '基本设置',
                    'icon' => null,
                    'url' => '/admin/config/basic/edit',
                    'children' => [],
                ],
                [
                    'title' => '语言',
                    'icon' => null,
                    'url' => '/admin/config/language/edit',
                    'children' => [],
                ],
            ],
        ],
    ],
];
