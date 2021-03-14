<?php

namespace July\Node\Seeds;

use Database\Seeds\SeederBase;
use Illuminate\Support\Facades\Date;

class CatalogSeeder extends SeederBase
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table = 'catalogs';

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        $records = [
            [
                'id' => 'main',
                'is_reserved' => true,
                'label' => '默认目录',
                'description' => '默认目录，不可删除',
            ],
        ];

        $share = [
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
        }, $records);
    }
}
