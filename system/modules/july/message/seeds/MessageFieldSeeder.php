<?php

namespace July\Message\Seeds;

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
     * 获取 node_fields 表数据
     *
     * @return array
     */
    public function getMessageFieldsTableRecords()
    {
        $records = [
            [
                'id' => 'subject',
                'field_type_id' => 'input',
                'label' => 'Subject',
                'is_required' => true,
            ],
            [
                'id' => 'email',
                'field_type_id' => 'input',
                'label' => 'E-mail',
                'is_required' => true,
            ],
            [
                'id' => 'name',
                'field_type_id' => 'input',
                'label' => 'Name',
            ],
            [
                'id' => 'phone',
                'field_type_id' => 'input',
                'label' => 'Phone',
            ],
            [
                'id' => 'message',
                'field_type_id' => 'text',
                'label' => 'Message',
                'is_required' => true,
            ],
        ];

        $now = Date::now();
        $share = [
            'langcode' => langcode('frontend'),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
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
