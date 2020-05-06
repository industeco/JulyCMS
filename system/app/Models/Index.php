<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'indexes';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'node_id',
        'node_field',
        'field_value',
        'langcode',
    ];
}
