<?php

return [
    // 登记实体
    'entities' => [
        //
    ],

    'field_types' => [
        //
    ],

    'field_parameters_schema' => [
        // 'type' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     'default' => 'string',
        // ],
        'maxlength' => [
            'cast' => 'int',
            'translatable' => false,
            'overwritable' => false,
        ],
        // 'multiple' => [
        //     'cast' => 'boolean',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     'default' => false,
        // ],

        'required' => [
            'cast' => 'boolean',
            'translatable' => false,
            'overwritable' => true,
            'default' => false,
        ],

        'default' => [
            'cast' => '@type',
            'translatable' => true,
            'overwritable' => true,
        ],
        'options' => [
            'cast' => '@type[]',
            'translatable' => true,
            'overwritable' => true,
        ],
        'placeholder' => [
            'cast' => 'string',
            'translatable' => true,
            'overwritable' => true,
        ],
        'helptext' => [
            'cast' => 'string',
            'translatable' => true,
            'overwritable' => true,
        ],

        // 'pattern' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        // ],
        // 'file_bundle' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     // 'enumerators' => ['image', 'file'],
        // ],
    ],
];
