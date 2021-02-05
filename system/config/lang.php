<?php

return [
    // 多语言开关
    'multiple' => false,

    // 后端资源默认语言
    'backend' => 'zh-Hans',

    // 前端资源默认语言
    'frontend' => 'en',

    // 内容编辑默认语言
    'content' => 'en',

    // 可用语言（代码）列表
    'available' => [
        'en' => [
            'accessible' => true,   // 前端可访问该语言页面
            'translatable' => true, // 内容可翻译为该语言
        ],
        'zh-Hans' => [
            'accessible' => false,
            'translatable' => false,
        ],
    ],

    // 可选语言（代码）列表
    'all' => [
        'ar' => [
            'name' => [
                'native' => 'العربية',
            ],
            'dir' => 'rtl',
        ],
        'de' => [
            'name' => [
                'native' => 'Deutsch',
            ],
            'dir' => 'ltr',
        ],
        'en' => [
            'name' => [
                'native' => 'English',
            ],
            'dir' => 'ltr',
        ],
        'es' => [
            'name' => [
                'native' => 'Español',
            ],
            'dir' => 'ltr',
        ],
        'fr' => [
            'name' => [
                'native' => 'français',
            ],
            'dir' => 'ltr',
        ],
        'hi' => [
            'name' => [
                'native' => 'हिन्दी, हिंदी',
            ],
            'dir' => 'ltr',
        ],
        'it' => [
            'name' => [
                'native' => 'Italiano',
            ],
            'dir' => 'ltr',
        ],
        'ja' => [
            'name' => [
                'native' => '日本語',
            ],
            'dir' => 'ltr',
        ],
        'pt' => [
            'name' => [
                'native' => 'Português',
            ],
            'dir' => 'ltr',
        ],
        'ru' => [
            'name' => [
                'native' => 'русский',
            ],
            'dir' => 'ltr',
        ],
        'zh-Hans' => [
            'name' => [
                'native' => '简体中文',
            ],
            'dir' => 'ltr',
        ],
    ],
];
