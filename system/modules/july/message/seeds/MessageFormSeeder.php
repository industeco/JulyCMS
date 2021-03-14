<?php

namespace July\Message\Seeds;

use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;

class MessageFormSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'message_forms';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        $records = [
            [
                'id' => 'new_message',
                'label' => '新消息',
                'description' => '默认表单，不可删除。',
                'is_reserved' => true,
            ],
        ];

        $share = [
            'langcode' => langcode('frontend'),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
        }, $records);
    }
}
