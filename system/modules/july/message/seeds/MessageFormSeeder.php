<?php

namespace July\Message\Seeds;

use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;

class MessageFormSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['message_forms'];

    /**
     * 获取 message_forms 表数据
     *
     * @return array
     */
    protected function getMessageFormsTableRecords()
    {
        $records = [
            [
                'id' => 'new_message',
                'label' => '新消息',
                'description' => '默认表单，不可删除。',
                'is_reserved' => true,
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
}
