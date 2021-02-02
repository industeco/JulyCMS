<?php

namespace July\Message\Seeds;

use App\Utils\Arr;
use Database\Seeds\SeederBase;

class MessageFieldMessageFormSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['message_field_message_form'];

    /**
     * 获取 message_field_message_form 表数据
     *
     * @return array
     */
    public function getMessageFieldMessageFormTableRecords()
    {
        // 所有字段信息
        $allFields = collect((new MessageFieldSeeder)->getMessageFieldsTableRecords())->keyBy('id')->map(function(array $field) {
            return Arr::only($field, ['label','description','is_required','helpertext','default_value','options','rules']);
        }, true);

        // 类型关联字段
        $molds = [
            'new_message' => ['subject', 'email', 'message'],
        ];

        // 生成记录
        $records = [];
        foreach ($molds as $id => $fields) {
            foreach ($fields as $index => $field) {
                $records[] = $allFields->get($field) + [
                    'mold_id' => $id,
                    'field_id' => $field,
                    'delta' => $index,
                ];
            }
        }

        return $records;
    }
}
