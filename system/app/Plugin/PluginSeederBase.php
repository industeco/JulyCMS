<?php

namespace App\Plugin;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PluginSeederBase extends Seeder
{
    /**
     * 待填充的数据库表
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
            foreach ($this->getRecords($table) as $record) {
                DB::table($table)->insert($record);
            }
        }
    }

    /**
     * 获取数据
     *
     * @param string $table
     * @return array
     */
    public function getRecords(string $table)
    {
        $method = 'get'. Str::studly($table).'Records';
        if ($method !== 'getRecords' && method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }
}
