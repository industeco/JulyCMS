<?php

namespace July\Core\Entity;

use App\Model as AppModel;
use App\Utils\Pocket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use July\Core\EntityField\EntityFieldBase;

abstract class ModelEntityBase extends EntityBase
{
    /**
     * 获取实体 id
     *
     * @return int|string
     */
    public function getEntityKey()
    {
        return $this->getKey();
    }

    /**
     * 查找实体
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \July\Core\Entity\EntityInterface|null
     */
    public static function find($id, array $columns = ['*'])
    {
        $instance = new static;
        return $instance->forwardCallTo($instance->newQuery(), 'find', [$id, $columns]);
    }

    /**
     * 查找实体，找不到则抛出错误
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \July\Core\Entity\EntityInterface|null
     *
     * @throws \July\Core\Entity\Exceptions\EntityNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*'])
    {
        try {
            $instance = new static;
            return $instance->forwardCallTo($instance->newQuery(), 'findOrFail', [$id, $columns]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                $th = Exceptions\EntityNotFoundException::wrap($th);
            }
            throw $th;
        }
    }

    /**
     * 获取常用属性
     *
     * @param  string|null $langcode
     * @return array
     */
    public function gather()
    {
        return array_merge(
            $this->attributesToArray(),
            $this->attachedAttributesToArray(),
            $this->entityFieldsToArray()
        );
    }

    /**
     * 获取固有属性集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectIntrinsicAttributes()
    {
        $attributes = collect([$this->getKeyName(), 'langcode']);
        if ($this->timestamps) {
            $attributes = $attributes->merge([
                $this->getUpdatedAtColumn(),
                $this->getCreatedAtColumn(),
            ]);
        }

        return $attributes->combine($attributes);
    }

    /**
     * 判断是否包含名为 {$key} 的固有属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasIntrinsicAttribute($key)
    {
        return in_array($key, $this->getCachedAttributeNames('intrinsicAttributes'));
    }

    /**
     * 获取固有属性的值
     *
     * @param  string $key
     * @return mixed
     */
    protected function getIntrinsicAttributeValue($key)
    {
        //
    }

    /**
     * 获取所有固有属性的值
     *
     * @return array
     */
    public function intrinsicAttributesToArray()
    {
        //
    }

    /**
     * 获取实体字段对象集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectEntityFields()
    {
        return collect();
    }

    /**
     * 判断是否包含名为 {$key} 的实体字段
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasEntityField($key)
    {
        return in_array($key, $this->getCachedAttributeNames('entityFields'));
    }

    /**
     * 获取实体字段的值
     *
     * @param  string $key 字段名
     * @return mixed
     */
    public function getEntityFieldValue($key)
    {
        /**
         * @var \July\Core\EntityField\EntityFieldBase
         */
        $field = $this->collectEntityFields()->get($key);

        return $this->transformModelValue($key, $field->getValue($this));
    }

    /**
     * 收集所有字段属性并化为数组
     *
     * @return array
     */
    public function entityFieldsToArray()
    {
        $attributes = [];

        // 获取原始字段值
        foreach ($this->collectEntityFields() as $field) {
            $attributes[$field->getEntityKey()] = $field->getValue($this);
        }

        // 对字段值做 mutate 转换（如果有对应的 getFieldNameAttribute 方法）
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // 对字段值做 cast 转换
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        return $attributes;
    }

    /**
     * 获取实体属性（可能是：固有属性，实体字段，或附加属性）
     *
     * @param  string  $key
     * @return mixed
     */
    public function getEntityAttribute($key)
    {
        if (! $key) {
            return;
        }

        // 尝试固有属性
        if ($this->hasIntrinsicAttribute($key)) {
            return $this->getIntrinsicAttributeValue($key);
        }

        // 尝试附加属性
        elseif ($this->hasAttachedAttribute($key)) {
            return $this->getAttachedAttributeValue($key);
        }

        // 尝试实体字段
        elseif ($this->hasEntityField($key)) {
            return $this->getEntityFieldValue($key);
        }

        return;
    }

    /**
     * 动态获取实体属性
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (! $key) {
            return;
        }

        return $this->getEntityAttribute($key) ?? $this->getAttribute($key);
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        return static::getEntityName();
    }

    /**
     * Retrieve the actual class name for a given morph class.
     *
     * @param  string  $class
     * @return string
     */
    public static function getActualClassNameForMorph($class)
    {
        if ($actualClass = Arr::get(Relation::morphMap() ?: [], $class, null)) {
            return $actualClass;
        }

        return EntityManager::resolveName($class) ?? $class;
    }
}
