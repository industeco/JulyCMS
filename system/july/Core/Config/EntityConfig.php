<?php

namespace July\Core\Config;

use App\Casts\Serialized;
use July\Core\Entity\EntityBase;

class EntityConfig extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'entity_configs';

    /**
     * 是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'langcode',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'config' => Serialized::class,
    ];

    /**
     * 根据实体路径查找配置项
     *
     * @param string $path
     * @return \Illuminate\Support\Collection
     */
    public static function findConfigByPath(string $path)
    {
        return static::query()->where('path', trim($path))->pluck('config', 'langcode');
    }
}
