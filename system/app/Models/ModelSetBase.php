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
     * Create a new collection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = [], $resolveModel = false)
    {
        $this->items = $this->getArrayableItems($items);

        if ($resolveModel) {
            // 解析模型
            $items = $this->resolveModels($this->items);

            // 模型主键名
            $key = static::getModelClass()::getModelKeyName();

            // 重新生成键名
            $this->items = collect($items)->keyBy($key)->all();
        }
    }

    /**
     * 获取指定模型实例并缓存
     *
     * @param  array $args
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetch(...$args)
    {
        return new static(real_args($args), true);
    }

    /**
     * 获取全部模型实例并缓存
     *
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetchAll()
    {
        return new static(static::getModelClass()::all(), true);
    }

    /**
     * 将参数转换为模型组
     *
     * @return \App\Models\ModelBase[]
     */
    protected function resolveModels(array $items)
    {
        if (empty($items)) {
            return [];
        }

        // 绑定的模型
        $class = static::getModelClass();

        $cache = self::$modelsCache[$class] ?? [];

        $models = [];
        foreach ($items as $item) {
            if (is_object($item) && ($item instanceof $class)) {
                $models['id_'.$item->getKey()] = $item;
            } elseif ($instance = $cache[$item] ?? null) {
                $models['id_'.$instance->getKey()] = $instance;
            } elseif ($instance = $class::find($item)) {
                $models['id_'.$instance->getKey()] = $instance;
            }
        }

        self::$modelsCache[$class] = array_merge($cache, $models);

        return $models;
    }


}
