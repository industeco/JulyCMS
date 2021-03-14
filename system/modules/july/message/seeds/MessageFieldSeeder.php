<?php

namespace July\Message\Seeds;

use App\EntityField\FieldTypes;
use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;
use July\Message\MessageField;

class MessageFieldSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'message_fields';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        $records = [
            [
                'id' => 'email',
                'field_type' => FieldTypes\Input::class,
                'label' => 'E-mail',
                'field_meta' => [
                    'required' => true,
                    'rules' => 'email',
                ],
            ],
            [
                'id' => 'name',
                'field_type' => FieldTypes\Input::class,
                'label' => 'Name',
                'field_meta' => [
                    'required' => true,
                    'rules' => 'max:35',
                ],
            ],
            [
                'id' => 'phone',
                'field_type' => FieldTypes\Input::class,
                'label' => 'Phone',
                'field_meta' => [
                    'rules' => 'max:35',
                ],
            ],
            [
                'id' => 'message',
                'field_type' => FieldTypes\Text::class,
                'label' => 'Message',
                'field_meta' => [
                    'required' => true,
                ],
            ],
        ];

        $share = [
            'langcode' => langcode('frontend'),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return array_merge($record, $share, [
                'field_meta' => isset($record['field_meta']) ? serialize($record['field_meta']) : null,
            ]);
        }, $records);
    }

    /**
     * {@inheritdoc}
     */
    public static function afterSeeding()
    {
        foreach (MessageField::all() as $field) {
            $field->tableUp();
        }
    }
}
