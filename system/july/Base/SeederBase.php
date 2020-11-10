<?php

namespace July\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class SeederBase extends Seeder implements SeederInterface
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = [];

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getRecords($table)
    {
        $method = 'get'. Str::studly($table).'Records';
        if ($method !== 'getRecords' && method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }
}
