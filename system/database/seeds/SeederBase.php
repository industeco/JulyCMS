<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class SeederBase extends Seeder
{
    /**
     * 指定数据表
     *
     * @var string|string[]
     */
    protected $table;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (static::getRecords() as $record) {
            DB::table($this->table)->insert($record);
        }
    }

    /**
     * 静态执行数据填充
     *
     * @return void
     */
    public static function seed()
    {
        (new static)->run();
    }

    /**
     * 获取初始数据
     *
     * @return array[]
     */
    public static function getRecords()
    {
        return [];
    }

    /**
     * 数据填充后执行
     */
    public static function afterSeeding()
    {
        //
    }
}
