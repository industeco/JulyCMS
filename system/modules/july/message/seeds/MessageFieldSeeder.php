<?php

namespace July\Message\Seeds;

use App\EntityField\FieldTypes;
use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;
use July\Message\MessageField;

class MessageFieldSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['message_fields'];

    /**
     * 获取 message_fields 表数据
     *
     * @return array
     */
    public function getMessageFieldsTableRecords()
    {
        $records = [
            [
                'id' => 'email',
                'field_type' => FieldTypes\Input::class,
                'label' => 'E-mail',
                'field_meta' => [
                    'required' => true,
                ],
            ],
            [
                'id' => 'name',
                'field_type' => FieldTypes\Input::class,
                'label' => 'Name',
            ],
            [
                'id' => 'phone',
                'field_type' => FieldTypes\Input::class,
                'label' => 'Phone',
            ],
            [
                'id' => 'message',
                'field_type' => 'text',
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
