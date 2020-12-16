<?php

namespace Specs;

use App\Casts\Serialized;
use Illuminate\Database\Eloquent\Model;

class SpecField extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'spec_fields';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'spec_id',
        'label',
        'description',
        'field_type',
        'default',
        'is_groupable',
        'is_searchable',
        'is_deleted',
        'delta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'default' => Serialized::class,
        'is_groupable' => 'bool',
        'is_searchable' => 'bool',
        'is_deleted' => 'bool',
    ];
}
