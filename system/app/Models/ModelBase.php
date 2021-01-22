<?php

namespace App\Models;

use App\Concerns\CacheGetTrait;
use App\Utils\Pocket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

abstract class ModelBase extends Model
{
    use CacheGetTrait;

    /**
     * （数据库表的）列名登记处
     *
     * @var array
     */
    protected static $columns = [];

    /**
     * 哪些字段可更新（白名单）
     *
     * @var array
     */
    protected $updateOnly = [];

    /**
     * 哪些字段不可更新（黑名单）
     *
     * @var array
     */
    protected $updateExcept = [];

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \App\Models\ModelBase|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function find($id, array $columns = ['*'])
    {
        $instance = new static;

        return $instance->forwardCallTo($instance->newQuery(), 'find', [$id, $columns]);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \App\Models\ModelBase|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*'])
    {
        $instance = new static;

        return $instance->forwardCallTo($instance->newQuery(), 'findOrFail', [$id, $columns]);
    }

    /**
     * Create and return an un-saved model instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function make(array $attributes = [])
    {
        return (new static)->newQuery()->make($attributes);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function create(array $attributes = [])
    {
        return tap((new static)->newQuery()->make($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        if ($this->updateOnly) {
            $attributes = Arr::only($attributes, $this->updateOnly);
        } elseif ($this->updateExcept) {
            $attributes = Arr::except($attributes, $this->updateExcept);
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * 获取所有列名
     *
     * @return array
     */
    public static function getColumns()
    {
        // 检查内存 $columns
        if (isset(self::$columns[static::class])) {
            return self::$columns[static::class];
        }

        // 检查缓存
        $pocket = new Pocket(static::class, 'columns');
        if ($columns = $pocket->get()) {
            return self::$columns[static::class] = $columns->value();
        }

        // 生成
        $columns = Schema::getColumnListing((new static)->getTable());

        // 保存到内存 $columns
        self::$columns[static::class] = $columns;

        // 缓存
        $pocket->put($columns);

        return $columns;
    }

    /**
     * 判断列是否存在
     *
     * @param  string $column
     * @return bool
     */
    public static function hasColumn(string $column)
    {
        return in_array($column, static::getColumns());
    }

    /**
     * 获取列值
     *
     * @param  string $column
     * @return mixed
     */
    public function getColumnValue(string $column)
    {
        return $this->transformModelValue($column, $this->attributes[$column] ?? null);
    }

    /**
     * 获取所有列值
     *
     * @return array
     */
    public function columnsToArray()
    {
        $attributes = [];
        foreach (static::getColumns() as $column) {
            $attributes[$column] = $this->attributes[$column] ?? null;
        }

        return $this->transformAttributesArray($attributes);
    }

    /**
     * 转换属性值
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributeValue($key, $value)
    {
        return $this->transformModelValue($key, $value);
    }

    /**
     * 转换属性数组
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributesArray(array $attributes)
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableItems($attributes)
        );

        // Add the mutated attributes to the attributes array.
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        return $attributes;
    }

    /**
     * 排除指定属性
     *
     * @param array $columns
     * @return array
     */
    public function except(array $columns = [])
    {
        return Arr::except($this->attributesToArray(), $columns);
    }
}
