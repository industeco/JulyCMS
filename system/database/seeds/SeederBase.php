<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class SeederBase extends Seeder
{
    /**
     * 待填充数据库表
     *
     * @var array
     */
    protected $tables = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->tables as $table) {
            foreach ($this->getTableRecords($table) as $record) {
                DB::table($table)->insert($record);
            }
        }
    }

    /**
     * 获取数据库表记录
     *
     * @param  string $table 表名
     * @return array[]
     */
    protected function getTableRecords(string $table)
    {
        $method = 'get'.Str::studly($table).'TableRecords';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return [];
    }
}
