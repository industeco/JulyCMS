<?php

namespace App\Models;

use App\Utils\Arr;
use Illuminate\Support\Collection;

abstract class ModelSetBase extends Collection
{
    /**
     * 缓存的模型实例
     *
     * @var array
     */
    protected static $modelsCache = [];

    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public static function getModelClass();

    /**
     * 获取指定模型实例并缓存
     *
     * @param  string|int|array|\App\Models\ModelBase|null
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetch($args = null)
    {
        // 格式化参数
        $args = Arr::wrap($args);

        $class = static::getModelClass();
        $cache = self::$modelsCache[$class] ?? [];
        if (empty($args)) {
            return new static($cache);
        }

        $models = [];
        foreach ($args as $arg) {
            if (is_object($arg) && ($arg instanceof $class)) {
                $models[$arg->getKey()] = $arg;
            } elseif (isset($cache[$arg])) {
                $arg = $cache[$arg];
                $models[$arg->getKey()] = $arg;
            } elseif ($instance = $class::find($arg)) {
                $models[$instance->getKey()] = $instance;
            }
        }

        self::$modelsCache[$class] = array_merge($cache, $models);

        return new static($class);
    }

    /**
     * 获取全部模型实例并缓存
     *
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetchAll()
    {
        $model = static::getModelClass();
        $cache = self::$modelsCache[$model] ?? [];
        foreach ($model::query()->whereKeyNot(array_keys($cache))->get() as $instance) {
            $cache[$instance->getKey()] = $instance;
        }
        self::$modelsCache[$model] = $cache;

        return new static($cache);
    }
}
