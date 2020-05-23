<?php

return [
    'categories' => [
        'files' => [
            'valid_mime' => [
                'application/pdf' => '.pdf',
                'application/msword' => '.doc',
                'application/vnd.ms-powerpoint' => '.ppt',
                'application/vnd.ms-excel' => '.xls',
                'application/x-dwg' => '.dwg',
            ],
        ],
        'images' => [
            'valid_mime' => [
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/gif' => '.gif',
                'image/svg+xml' => '.svg',
                'image/x-icon' => '.ico',
                'image/bmp' => '.bmp',
                'image/webp' => '.webp',
            ],
        ],
    ],
];
