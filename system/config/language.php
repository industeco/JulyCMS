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

    // 可用语言列表
    'langcodes' => [
        'en' => [
            'accessible' => true,   // 前端可访问该语言页面
            'translatable' => true, // 内容可翻译为该语言
        ],
    ],
];
