<?php

namespace App\Models;

use App\Support\SetBase;
use App\Support\Translation\TranslatableInterface;

abstract class ModelSetBase extends SetBase
{
    /**
     * 缓存的实例
     *
     * @var array
     */
    protected static $modelsCache = [];

    const PROCESS_TRANSLATE = 8;

    /**
     * The items contained in the collection.
     *
     * @var \App\Models\ModelBase[]
     */
    protected $items = [];

    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public static function getModelClass();

    /**
     * @param  mixed  $items
     * @param  int $process = 0
     * @return array
     */
    protected static function processItems($items, $process)
    {
        $items = parent::processItems($items, $process);

        if ($process & static::PROCESS_TRANSLATE) {
            $items = static::translateModels($items);
        }

        return $items;
    }

    /**
     * 验证是否可作为集合项
     *
     * @param  mixed $item
     * @return bool
     */
    public static function isValidItem($item)
    {
        $class = static::getModelClass();

        return is_object($item) && ($item instanceof $class);
    }

    /**
     * 验证是否可作为集合项
     *
     * @param  mixed $item
     * @return bool
     */
    public static function getItemKey($item)
    {
        return $item->getKey();
    }

    /**
     * @return bool
     */
    public static function isTranslatable()
    {
        return is_subclass_of(static::getModelClass(), TranslatableInterface::class);
    }

    /**
     * @param  array|\App\Models\ModelBase[] $models
     * @return array|\App\Models\ModelBase[]
     */
    public static function translateModels(array $models)
    {
        if (static::isTranslatable() && $langcode = langcode('rendering')) {
            foreach ($models as $model) {
                $model->translateTo($langcode);
            }
        }

        return $models;
    }

    /**
     * 获取实例并缓存
     *
     * @param  mixed $item
     * @return \App\Models\ModelBase|null
     */
    public static function resolveItem($item)
    {
        $class = static::getModelClass();

        $model = is_object($item) && ($item instanceof $class)
                    ? $item
                    : (self::$modelsCache[$class.'/'.$item] ?? $class::find($item));

        if ($model) {
            self::$modelsCache[$class.'/'.$model->getKey()] = $model;
            if (static::isTranslatable() && $langcode = config('lang.rendering')) {
                $model->translateTo($langcode);
            }

            return $model;
        }

        return null;
    }

    /**
     * 创建 ModelSet
     *
     * @return static|\App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetch()
    {
        $args = func_get_args();

        return new static(real_args($args));
    }

    /**
     * 创建 ModelSet，包含全部模型实例
     *
     * @return static|\App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetchAll()
    {
        return new static(static::getModelClass()::all()->all(), static::PROCESS_INDEX | static::PROCESS_TRANSLATE);
    }
}
