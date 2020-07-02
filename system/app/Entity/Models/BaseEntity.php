<?php

namespace App\Entity\Models;

use App\Entity\Contracts\Entity;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

abstract class BaseEntity extends BaseModel implements Entity
{
    /**
     * 按类别统计
     *
     * @return array
     */
    public static function countByType()
    {
        $table = (new static)->getTable();
        $columnType = static::getEntityId().'_type';

        $sql = "SELECT `{$columnType}`, count(`{$columnType}`) as `total` FROM `{$table}` GROUP BY `{$columnType}`";
        return collect(DB::select($sql))->pluck('total', $columnType)->all();
    }
}
