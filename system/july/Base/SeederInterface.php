<?php

namespace July\Base;

interface SeederInterface
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run();

    /**
     * 获取数据
     *
     * @param string $table
     * @return array
     */
    public function getRecords($table);

    // /**
    //  * 定义数据填充后的动作
    //  *
    //  * @return void
    //  */
    // public static function afterSeeding();
}
