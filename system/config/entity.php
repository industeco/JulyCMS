<?php

return [
    // 登记实体
    'entities' => [
        //
    ],

    // 登记实体字段类型
    'field_types' => [
        //
    ],

    'field_parameters_schema' => [
        // 改为在字段类型中定义
        // 'type' => [
        //     'cast' => 'string',
        //     'overwritable' => false,
        //     'translatable' => false,
        //     'default' => 'string',
        // ],

        // 'multiple' => [
        //     'cast' => 'boolean',
        //     'overwritable' => false,
        //     'translatable' => false,
        //     'default' => false,
        // ],

        // field_handle | bundle_handle | bundle_langcode
        // field_handle | bundle_handle | bundle_original_langcode
        // field_handle | null | bundle_langcode
        // field_handle | null | field_original_langcode

        // 改为记录到字段表中
        // 'maxlength' => [
        //     'cast' => 'int',
        //     'overwritable' => false,
        //     'translatable' => false,
        // ],

        // 改为记录到字段表和实体类型表中
        // 'required' => [
        //     'cast' => 'boolean',
        //     'overwritable' => true,
        //     'translatable' => false,
        // ],
        // 'helpertext' => [
        //     'cast' => 'string',
        //     'overwritable' => true,
        //     'translatable' => false,
        // ],

        'default' => [
            'cast' => '@type',
            'overwritable' => true,
            'translatable' => true,
        ],
        'options' => [
            'cast' => '@type[]',
            'overwritable' => true,
            'translatable' => true,
        ],
        'placeholder' => [
            'cast' => 'string',
            'overwritable' => true,
            'translatable' => true,
        ],

        // 'pattern' => [
        //     'cast' => 'string',
        //     'overwritable' => false,
        //     'translatable' => false,
        // ],

        // 'file_bundle' => [
        //     'cast' => 'string',
        //     'overwritable' => false,
        //     'translatable' => false,
        //     // 'enumerators' => ['image', 'file'],
        // ],
    ],
];
