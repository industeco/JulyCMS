<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use stdClass;

class Records
{
    protected $records = [];

    protected $groupable = [];

    protected $searchable = [];

    public function __construct(array $records = [])
    {
        $this->records = array_map(function($record) {
            return (array) $record;
        }, $records);
    }

    /**
     * 在指定的表中，获取符合指定条件的记录
     *
     * @param  string $table
     * @param  array|null $conditions
     * @return static
     */
    public static function search(string $table, ?array $conditions = null)
    {
        if ($conditions = $conditions ?? static::getConditionsFromRequest()) {
            //
        }

        return new static(DB::table($table)->get()->all());
    }

    /**
     * 从 Request 中获取查询条件
     *
     * @return array
     */
    public static function getConditionsFromRequest()
    {
        //
    }
}
