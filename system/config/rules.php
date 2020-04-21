<?php

return [
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
];
