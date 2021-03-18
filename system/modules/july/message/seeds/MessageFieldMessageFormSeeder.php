<?php

namespace July\Message\Seeds;

use App\Support\Arr;
use Database\Seeds\SeederBase;

class MessageFieldMessageFormSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'message_field_message_form';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        // 所有字段
        $allFields = [];

        foreach (MessageFieldSeeder::getRecords() as $field) {
            $allFields[$field['id']] = Arr::only($field, [
                'label',
                'description',
                'field_meta',
            ]);
        }

        // 类型关联字段
        $molds = [
            'new_message' => ['email', 'message'],
        ];

        // 生成记录
        $records = [];
        foreach ($molds as $id => $fields) {
            foreach ($fields as $index => $field) {
                $records[] = array_merge($allFields[$field], [
                    'mold_id' => $id,
                    'field_id' => $field,
                    'delta' => $index,
                ]);
            }
        }

        return $records;
    }
}
